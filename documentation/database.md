# Database Module Guide

The database layer centralizes connection handling through `DatabaseHandler` instances registered in a service container and exposes helper methods via `SQLManager`.

## Creating Configuration via CLI

Generate a database configuration interactively:

```bash
php coriander make:database
```

The command prompts for MySQL or SQLite and writes the appropriate settings to `config/database.php`.

## Configuration

Database settings can come from `.env` or `config/database.php`.

For local projects, `.env` is usually enough:

```env
DB_TYPE=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_CHARSET=utf8mb4
DB_NAME=app
DB_USER=user
DB_PASSWORD=secret
```

For projects that need PHP-level configuration, create `config/database.php` with the required constants:

```php
<?php
// config/database.php

define('DB_TYPE', 'mysql');          // or 'sqlite'
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');
define('DB_NAME', 'app');
define('DB_USER', 'user');
define('DB_PASSWORD', 'secret');
```

The file is automatically loaded from `config/config.php` when present. Constants already defined in `config/database.php` take precedence over `.env` values. Avoid committing credentials to version control.

## Migrations

CorianderPHP supports timestamped migration files with batch tracking.

### Create migration files

```bash
php coriander make:migration CreateUsersTable
```

This creates a file in `database/migrations` named like:

`20260305123000_create_users_table.php`

Each migration file returns an object with `up(PDO $pdo)` and optional `down(PDO $pdo)` methods.

### Apply migrations

```bash
php coriander migrate
```

### Check migration status

```bash
php coriander migrate:status
```

### Rollback migrations

```bash
php coriander migrate:rollback
php coriander migrate:rollback --step=2
```

### Migration safety notes

- Executed migration checksums are tracked in the `migrations` table.
- If an executed migration file changes, commands fail by default.
- Use `--allow-changed` only in local development when intentionally editing history.
- Keep migrations immutable in shared/staging/production environments.

## Error Handling

- `DatabaseHandler` logs warnings if required constants are missing, unsupported drivers are used, or the connection cannot be established.
- Wrap query calls in `try/catch` blocks and log exceptions to avoid exposing details:

```php
use CorianderCore\Core\Database\SQLManager;
use CorianderCore\Core\Database\DatabaseException;

try {
    $users = SQLManager::findAll('users');
} catch (DatabaseException $e) {
    // handle or log error
}
```

## Best Practices

- Use prepared statements and parameter binding to prevent SQL injection.
- Prefer `findWhere`, `updateWhere`, and `deleteWhere` when your conditions are simple equality checks.
- Use `sqlScript()` for joins, ordering, grouping, ranges, raw SQL expressions, or write statements that do not fit the simple helpers.
- Keep long-lived connections to a minimum; enable `DatabaseHandler::setAutoCloseConnection(false)` only when necessary.
- Centralize complex queries in repository classes to maintain SOLID principles.
- Close connections explicitly in long-running scripts.

## Usage Examples

Use `findAll(['col1', 'col2'], $table)` when you want an explicit column list.
`findAll($table)` is the concise all-columns signature.
`findAll(['*'], $table)` remains available for compatibility but is not recommended.

### Selecting Records

```php
use CorianderCore\Core\Database\SQLManager;

$activeUsers = SQLManager::findWhere(
    ['id', 'email'],
    'users',
    ['status' => 'active']
);
```
Fetches the ID and email for every user marked as active.

### Inserting Records

```php
use CorianderCore\Core\Database\SQLManager;

SQLManager::insertInto('users', [
    'email' => 'john@example.com',
    'status' => 'active',
]);
```
Creates a user record with the provided email and marks it as active.

### Updating Records

```php
use CorianderCore\Core\Database\SQLManager;

SQLManager::updateWhere('users', ['status' => 'disabled'], ['id' => 5]);
```
Disables the user whose ID equals `5`.

### Deleting Records

```php
use CorianderCore\Core\Database\SQLManager;

SQLManager::deleteWhere('users', ['status' => 'inactive']);
```
Removes all users currently flagged as inactive.

### Custom SQL

Use `sqlScript()` when a repository needs joins, grouping, ordering, ranges, or other SQL that does not fit the simple helpers.

```php
$rows = SQLManager::sqlScript(
    'SELECT users.id, users.email, COUNT(topics.id) AS topic_count
     FROM users
     LEFT JOIN topics ON topics.user_id = users.id
     WHERE users.created_at >= :from
     GROUP BY users.id
     ORDER BY topic_count DESC',
    ['from' => '2026-01-01']
);
```

`sqlScript()` uses prepared statements and bound parameters. Its return shape depends on the statement result:

```php
[] // SELECT returned no rows
```

```php
['id' => 1, 'email' => 'admin@example.com'] // SELECT returned one row
```

```php
[
    ['id' => 1, 'email' => 'admin@example.com'],
    ['id' => 2, 'email' => 'user@example.com'],
] // SELECT returned multiple rows
```

Write statements return `true` when execution succeeds:

```php
SQLManager::sqlScript(
    'UPDATE users SET status = :status WHERE id = :id',
    ['status' => 'disabled', 'id' => 5]
);
```

When a query may return one or many rows, normalize the result inside the repository:

```php
private function rows(array|bool $result): array
{
    if ($result === true || $result === []) {
        return [];
    }

    return array_is_list($result) ? $result : [$result];
}
```

For one-row lookups, add `LIMIT 1` and return `null` for the empty array:

```php
$row = SQLManager::sqlScript(
    'SELECT id, email FROM users WHERE id = :id LIMIT 1',
    ['id' => $id]
);

return $row === [] ? null : $row;
```
