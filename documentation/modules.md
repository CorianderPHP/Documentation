# Custom Module Guide

Modules are reusable project services or packages. User-created modules should live in the app-owned `src/Modules` directory so framework updates never overwrite them.

Framework or official reusable modules can still live under `CorianderCore/modules`, but application code should not be added there.

## Build A Working Custom Module

This example exposes a read-only `/posts` JSON endpoint backed by SQLite. It needs an installed framework project, Composer dependencies, and a configured database. Follow [Installation](/documentation/installation) first. Use a new learning database: the migration below creates a `posts` table and inserts sample data.

`PostRepository` is a class you create, not a built-in framework feature. The starter's Composer mapping `"App\\": "src/"` loads it from `src/Modules/Blog`. Custom modules are ordinary PHP classes; only method files under `src/Views` expose URLs. Run `composer dump-autoload` if you change the mapping.

### Create The Schema

For this SQLite example, set `DB_TYPE=sqlite` and `DB_NAME=database/app.sqlite` in `.env`. Ensure `database` exists and is writable. Do not also define conflicting database constants in `config/database.php`.

```bash
php coriander make:migration CreatePostsTable
```

Replace the generated file in `database/migrations` with this complete migration, keeping its generated filename:

```php
<?php
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            body TEXT NOT NULL,
            status TEXT NOT NULL,
            created_at TEXT NOT NULL
        )');
        $statement = $pdo->prepare('INSERT INTO posts (title, body, status, created_at)
            VALUES (:title, :body, :status, :created_at)');
        $statement->execute([
            'title' => 'First post', 'body' => 'A working module example.',
            'status' => 'published', 'created_at' => '2026-01-01 12:00:00',
        ]);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE posts');
    }
};
```

```bash
php coriander migrate
```

The fixed sample row belongs to this learning example, not a recommended production seeding strategy. For MySQL, use `id INTEGER PRIMARY KEY AUTO_INCREMENT`, bounded `VARCHAR` columns for title/status, and `DATETIME` for `created_at`. Review migration rollback behavior in [Database](/documentation/database#migration-safety-notes).

### Create The Repository

Create `src/Modules/Blog/PostRepository.php` with this complete class:

```php
<?php
declare(strict_types=1);

namespace App\Modules\Blog;

use CorianderCore\Core\Database\SQLManager;

final class PostRepository
{
    public function published(): array
    {
        $result = SQLManager::sqlScript(
            'SELECT id, title, body FROM posts WHERE status = :status ORDER BY id DESC',
            ['status' => 'published']
        );
        return $result === [] ? [] : (array_is_list($result) ? $result : [$result]);
    }
}
```

`sqlScript()` returns one row as an associative array and several rows as a list. This repository normalizes both into a list, so callers do not need to guess the result shape. The SELECT cannot return a write-statement boolean.

### Call It From A Handler

```bash
php coriander make:route posts.get
```

Replace `src/Views/posts.get.php` with this complete handler:

```php
<?php
declare(strict_types=1);

use App\Modules\Blog\PostRepository;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    $posts = (new PostRepository())->published();
    return Responses::json(['data' => $posts]);
};
```

The router calls the handler, the handler calls your repository, and the repository uses the framework database API. The handler chooses the JSON response; the repository knows nothing about HTTP.

### Verify The Result

Visit `/posts`. Expect HTTP 200 with `data` containing the seeded post. If no rows are published, `data` is `[]`. An unavailable database is an error, not an empty result: inspect the application log and database configuration rather than hiding the exception.

For an HTML page, pass this prepared list to an unsuffixed template with `Responses::view()`; see [Dynamic Views](/documentation/dynamic-views). To add pagination or a workflow that writes several records together, continue with [Database Patterns](/documentation/database-patterns).

## When To Add A Service

Keep SQL in repositories. Add an app-owned service when a use case coordinates validation, permissions, or multiple repository operations, such as publishing a post and recording an audit event. A service is also an ordinary PHP class, not something the framework discovers automatically. See the complete [transaction example](/documentation/database-patterns#transactions).

## Framework Modules

`CorianderCore/modules` is reserved for framework-owned or official modules using the `CorianderCore\Modules\` namespace.

Do not place project-specific code there. The folder is inside the framework-managed `CorianderCore` tree and should be treated as core-owned.

## Best Practices

- Put application modules in `src/Modules`.
- Keep modules self-contained and reusable.
- Use descriptive namespaces and follow PSR-4 conventions.
- Keep framework-owned code under `CorianderCore`, and project-owned code under `src`.
- Document module APIs and include tests when the module contains important business logic.
