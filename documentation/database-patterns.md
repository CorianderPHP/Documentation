# Database Patterns

The database API gives you two useful levels: simple helpers for common CRUD, and `sqlScript()` for readable custom SQL.

## Recommended Decision

Use migrations for schema.

Use `SQLManager` helpers when the query is simple.

Use `sqlScript()` when the query needs joins, grouping, ordering, limits, ranges, write statements, or SQL that should remain readable.

Use repositories when the same data access appears in more than one controller.

## Migrations Own Schema

Create schema changes with:

```bash
php coriander make:migration create_posts_table
php coriander migrate
```

Do not create tables from controllers. Controllers handle requests. Migrations describe database structure.

## Simple Helpers

Use helpers for direct equality conditions:

```php
use CorianderCore\Core\Database\SQLManager;

$post = SQLManager::findWhere(
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

Keep SQL in a repository:

```php
namespace Modules\Blog;

use CorianderCore\Core\Database\SQLManager;

final class BlogRepository
{
    public function create(array $data): bool
    {
        return SQLManager::insertInto('posts', [
            'title' => $data['title'],
            'body' => $data['body'],
            'status' => 'draft',
        ]);
    }

    public function published(): array
    {
        return $this->rows(SQLManager::sqlScript(
            'SELECT id, title FROM posts WHERE status = :status ORDER BY created_at DESC',
            ['status' => 'published']
        ));
    }

    private function rows(array|bool $result): array
    {
        if ($result === true || $result === []) {
            return [];
        }

        return array_is_list($result) ? $result : [$result];
    }
}
```

Controllers should call `BlogRepository`, not build SQL.

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

Use a transaction when several writes must succeed together.

If a framework helper does not express the workflow clearly, use PDO through the database handler or a dedicated module. Keep transaction boundaries in services, not views.

## Common Mistakes

- Writing SQL directly in views.
- Mixing validation, permissions, and SQL inside one controller method.
- Editing old migrations after they ran in shared environments.
- Using raw condition strings when equality helper methods would work.
- Returning mixed `sqlScript()` shapes directly to views.
