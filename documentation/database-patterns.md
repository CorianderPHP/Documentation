# Database Patterns

The database API gives you two useful levels: simple helpers for common CRUD, and `sqlScript()` for readable custom SQL.

## Recommended Decision

Use migrations for schema.

Use `SQLManager` helpers when the query is simple.

Use `sqlScript()` when the query needs joins, grouping, ordering, limits, ranges, write statements, or SQL that should remain readable.

Use repositories when the same data access appears in more than one handler.

## Migrations Own Schema

Create schema changes with:

```bash
php coriander make:migration create_posts_table
php coriander migrate
```

Do not create tables from request handlers. Request Handlers handle requests. Migrations describe database structure.

## Simple Helpers

Use helpers for direct equality conditions:

```php
use CorianderCore\Core\Database\SQLManager;

$posts = SQLManager::findWhere(
    ['id', 'title', 'status'],
    'posts',
    ['id' => 10]
);
```

Good helper use cases:

- find one row by ID
- list rows by status
- insert simple records
- update by ID
- delete by ID or status

`findWhere()` always returns a list, including for a unique ID. Read `$posts[0] ?? null` when your schema guarantees at most one match. This differs from `sqlScript()` below.

## Custom SQL With sqlScript

Use `sqlScript()` when helper calls make the query harder to understand.

```php
$rows = SQLManager::sqlScript(
    'SELECT posts.id, posts.title, users.email AS author_email
     FROM posts
     INNER JOIN users ON users.id = posts.author_id
     WHERE posts.status = :status
     ORDER BY posts.created_at DESC
     LIMIT 20',
    ['status' => 'published']
);
```

The SQL stays visible, parameters stay bound, and the repository method stays easy to read.

## Normalize Return Shapes

`sqlScript()` returns:

- `[]` for no selected rows
- one associative array for one selected row
- a list of associative arrays for many rows
- `true` for successful write statements

Normalize in repositories:

```php
private function rows(array|bool $result): array
{
    if ($result === true || $result === []) {
        return [];
    }

    return array_is_list($result) ? $result : [$result];
}
```

For one-row lookups:

```php
public function find(int $id): ?array
{
    $row = SQLManager::sqlScript(
        'SELECT id, title FROM posts WHERE id = :id LIMIT 1',
        ['id' => $id]
    );

    return $row === [] ? null : $row;
}
```

## Repository Shape

Keep SQL in a repository, not in a view. The [Custom Module Guide](/documentation/modules#build-a-working-custom-module) provides a complete SQLite migration, `PostRepository`, handler, and expected response. The examples above are query extracts: their tables and columns must already exist in your application. In particular, the join requires a `users` table and a `posts.author_id` column; those are not part of the small module example.

## Pagination

Start with the working module example. Add this method **inside** `src/Modules/Blog/PostRepository.php`; it uses that example's `posts` schema:

```php
public function publishedPage(int $page, int $perPage = 20): array
{
    $page = max(1, min($page, 100000));
    $perPage = max(1, min($perPage, 100));
    $count = SQLManager::sqlScript(
        'SELECT COUNT(*) AS total FROM posts WHERE status = :status',
        ['status' => 'published']
    );
    $total = (int) $count['total'];
    $offset = ($page - 1) * $perPage;
    $result = SQLManager::sqlScript(
        "SELECT id, title, body FROM posts WHERE status = :status
         ORDER BY id DESC LIMIT $perPage OFFSET $offset",
        ['status' => 'published']
    );
    $rows = $result === [] ? [] : (array_is_list($result) ? $result : [$result]);
    return [
        'data' => $rows, 'page' => $page, 'per_page' => $perPage,
        'total' => $total, 'last_page' => max(1, (int) ceil($total / $perPage)),
    ];
}
```

Only bounded integers computed by this method enter `LIMIT` and `OFFSET`; the status stays a bound parameter. Never interpolate raw query-string input. This avoids driver-dependent string binding for limit parameters. Ordering by the unique ID makes page order predictable. Large-offset pagination can become expensive; cursor pagination is a separate design for larger datasets.

Replace `src/Views/posts.get.php` with this complete handler:

```php
<?php
declare(strict_types=1);

use App\Modules\Blog\PostRepository;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    $query = $request->getQueryParams();
    $rawPage = $query['page'] ?? '1';
    $rawSize = $query['per_page'] ?? '20';
    $page = is_string($rawPage) ? filter_var($rawPage, FILTER_VALIDATE_INT) : false;
    $size = is_string($rawSize) ? filter_var($rawSize, FILTER_VALIDATE_INT) : false;
    if ($page === false || $page < 1 || $page > 100000
        || $size === false || $size < 1 || $size > 100) {
        return Responses::json(['error' => 'page must be 1-100000; per_page must be 1-100.'], 422);
    }
    return Responses::json((new PostRepository())->publishedPage($page, $size));
};
```

Verify `/posts?page=1&per_page=1`: the module's seed produces one row, `total: 1`, and `last_page: 1`. `/posts?page=2&per_page=1` returns `data: []` while preserving the total. `/posts?page[]=1`, `/posts?page=0`, and `/posts?per_page=101` return 422, not a PHP type error. Counts and rows are separate reads, so simultaneous writes can change them between queries; this is ordinary pagination, not a snapshot guarantee.

## SQLite And MySQL

SQLite is good for:

- local learning
- demos
- small single-server apps
- tests

MySQL is usually better for:

- multi-user production apps
- larger data volume
- managed hosting
- applications with existing MySQL tooling

Keep SQL portable where possible. When SQLite and MySQL need different SQL, isolate that difference in the repository or migration.

## Transactions

Use a transaction when several writes must succeed together. For example, publishing a post and recording its audit event should either both persist or both roll back.

### Add The Audit Table

After the module example's posts migration, generate another migration:

```bash
php coriander make:migration CreatePostEventsTable
```

Replace the generated file with this complete SQLite migration, then run `php coriander migrate`:

```php
<?php
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE post_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            event TEXT NOT NULL
        )');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE post_events');
    }
};
```

For MySQL, use `AUTO_INCREMENT` and appropriate column types; both tables must use a transactional engine such as InnoDB. This example records an event, not a complete audit system with actors, timestamps, and retention policies.

### Create The Write Service

Create `src/Modules/Blog/PublishPost.php` with this complete app-owned class:

```php
<?php
declare(strict_types=1);

namespace App\Modules\Blog;

use CorianderCore\Core\Database\DatabaseHandler;
use PDO;
use RuntimeException;
use Throwable;

final class PublishPost
{
    public function __construct(private readonly DatabaseHandler $database)
    {
    }

    public function publish(int $id): void
    {
        $pdo = $this->database->getPDO();
        if (!$pdo instanceof PDO) {
            throw new RuntimeException('The database connection is unavailable.');
        }
        if ($pdo->inTransaction()) {
            throw new RuntimeException('This service must own its transaction.');
        }
        $pdo->beginTransaction();
        try {
            $find = $pdo->prepare('SELECT id FROM posts WHERE id = :id');
            $find->execute(['id' => $id]);
            if ($find->fetchColumn() === false) {
                throw new RuntimeException('Post not found.');
            }
            $update = $pdo->prepare('UPDATE posts SET status = :status WHERE id = :id');
            $update->execute(['status' => 'published', 'id' => $id]);
            $event = $pdo->prepare('INSERT INTO post_events (post_id, event) VALUES (:id, :event)');
            $event->execute(['id' => $id, 'event' => 'published']);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }
}
```

Call it from authorized application code with a validated positive ID. This usage is an extract, not a publicly writable route:

```php
use App\Modules\Blog\PublishPost;
use CorianderCore\Core\Database\DatabaseHandler;

$database = new DatabaseHandler();
try {
    (new PublishPost($database))->publish(1);
} finally {
    $database->close();
}
```

All statements run on the **same PDO connection** supplied by `DatabaseHandler::getPDO()`. Do not construct a new handler for each write or close it inside the transaction. `SQLManager` can use a separately configured handler; if you mix its helpers into this workflow, first inject this exact instance using `SQLManager::setDatabaseHandler($database)`. Never replace that shared handler mid-transaction. The example deliberately uses PDO throughout to make connection ownership explicit; there is no framework `transaction()` helper being assumed.

The default `close()` policy releases the handler's PDO reference. If your application disables auto-close, `close()` is a no-op until you re-enable it on the instance. Long-running workers must deliberately manage their connection lifecycle.

### Verify Commit And Rollback

In a disposable database, create a draft post and call `publish()` with its ID. Expect `status = published` and one event row. To verify atomic failure, remove the audit table **in the disposable test database only**, then repeat with another draft post: the insert fails, the exception propagates, and the post stays draft. Never perform this failure check on shared or production data.

Keep permission checks before this call and translate expected application errors into appropriate responses in the handler. Unexpected database exceptions should be logged without exposing SQL or credentials. This is a single-connection transaction example, not a concurrency-safe workflow for inventory, balances, or repeated publication; those also require app-specific constraints and locking. Do not put schema changes inside it: MySQL DDL can commit implicitly.

## Common Mistakes

- Writing SQL directly in views.
- Mixing validation, permissions, and SQL inside one handler method.
- Editing old migrations after they ran in shared environments.
- Using raw condition strings when equality helper methods would work.
- Returning mixed `sqlScript()` shapes directly to views.
