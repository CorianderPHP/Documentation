# SQLite Data Model

The forum should use a real database from the start. SQLite is the easiest first choice because it needs no server, works well for local learning, and lets the rest of the app use the same repository shape you would use with MySQL later.

## Goal

Create database configuration, forum tables, seed accounts, and repositories for users, categories, topics, and replies.

## Files Created

```structure
database/forum.sqlite
database/migrations/
src/Modules/ForumDemo/Data/UserRepository.php
src/Modules/ForumDemo/Data/ForumRepository.php
```

## Relationship Map

The forum uses a small relational model:

```txt
users
  -> topics.user_id
  -> replies.user_id
  -> moderation_events.admin_user_id

categories
  -> topics.category_id

topics
  -> replies.topic_id
  -> moderation_events target_type=topic

replies
  -> moderation_events target_type=reply
```

Read this as ownership and lookup flow:

- A category groups topics.
- A topic has one author and many replies.
- A reply has one author and belongs to one topic.
- Moderation events point at the affected topic or reply.
- User roles are stored on users, but permission decisions still belong in `DemoPermissionService`.

This keeps the schema normal enough to migrate to MySQL later without changing controller or view structure.

## Step: Configure SQLite

Generate or edit the database configuration with the framework CLI:

```bash
php coriander make:database
```

Choose SQLite when prompted. The resulting values should point to an app-owned database file:

```env
DB_TYPE=sqlite
DB_NAME=database/forum.sqlite
```

Create the folder if it does not exist:

```bash
mkdir database
```

SQLite can create the file on first connection, but creating the folder explicitly avoids path errors.

## Step: Create A Migration

Generate a migration for the forum tables:

```bash
php coriander make:migration CreateForumTables
```

Use the migration to create these tables:

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('admin', 'member')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE topics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    body TEXT NOT NULL,
    locked INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE replies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    topic_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    body TEXT NOT NULL,
    is_deleted INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE moderation_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_user_id INTEGER NOT NULL,
    target_type TEXT NOT NULL,
    target_id INTEGER NOT NULL,
    action TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES users(id)
);
```

Then run:

```bash
php coriander migrate
```

## Step: Seed Demo Accounts

Seed two accounts so the authentication chapter has predictable users:

```sql
INSERT INTO users (name, email, password_hash, role) VALUES
('Mira Admin', 'admin@example.com', '$2y$10$replace-with-a-real-hash', 'admin'),
('Sam Member', 'user@example.com', '$2y$10$replace-with-a-real-hash', 'member');
```

Use `password_hash()` to create the values:

```php
echo password_hash('demo-admin', PASSWORD_DEFAULT);
echo password_hash('demo-user', PASSWORD_DEFAULT);
```

For a documentation-only local project, fixed demo passwords are acceptable. Do not store plain text passwords.

## Step: Seed Forum Content

Create enough content to render the first screens:

```sql
INSERT INTO categories (name, description) VALUES
('Getting Started', 'First project questions.'),
('Controllers and Routes', 'Request flow discussions.');

INSERT INTO topics (category_id, user_id, title, body) VALUES
(1, 2, 'How do I create my first view?', 'I want to understand where templates live.'),
(2, 1, 'Where should route files live?', 'How do I keep feature routes organized?');

INSERT INTO replies (topic_id, user_id, body) VALUES
(1, 1, 'Create the view under public/public_views and render it from a controller.'),
(1, 2, 'Keep the template simple and pass prepared data from the controller.'),
(2, 1, 'Use an app-owned route file under src/Routes and include it from public/routes.php.');
```

## Step: Create UserRepository

Create `src/Modules/ForumDemo/Data/UserRepository.php`.

```php
<?php
declare(strict_types=1);

namespace Modules\ForumDemo\Data;

use CorianderCore\Core\Database\SQLManager;

final class UserRepository
{
    public function find(int $id): ?array
    {
        $row = SQLManager::sqlScript(
            'SELECT id, name, email, password_hash, role FROM users WHERE id = :id LIMIT 1',
            ['id' => $id]
        );

        return $row === [] ? null : $row;
    }

    public function findByEmail(string $email): ?array
    {
        $row = SQLManager::sqlScript(
            'SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );

        return $row === [] ? null : $row;
    }

    public function all(): array
    {
        return SQLManager::findAll(['id', 'name', 'email', 'role'], 'users');
    }

    public function findByRole(string $role): ?array
    {
        $row = SQLManager::sqlScript(
            'SELECT id, name, email, role FROM users WHERE role = :role LIMIT 1',
            ['role' => $role]
        );

        return $row === [] ? null : $row;
    }
}
```

If your framework version exposes a different `SQLManager` method name, keep the repository interface and adapt only the SQL calls inside this class. Controllers should not care how rows are loaded.

## Step: Create ForumRepository

Create `src/Modules/ForumDemo/Data/ForumRepository.php`.

```php
<?php
declare(strict_types=1);

namespace Modules\ForumDemo\Data;

use CorianderCore\Core\Database\SQLManager;

final class ForumRepository
{
    public function categories(): array
    {
        return SQLManager::findAll(['id', 'name', 'description'], 'categories');
    }

    public function topics(): array
    {
        return $this->rows(SQLManager::sqlScript(
            'SELECT topics.id, topics.category_id, topics.title, topics.created_at, users.name AS author_name,
                    COUNT(replies.id) AS reply_count
             FROM topics
             INNER JOIN users ON users.id = topics.user_id
             LEFT JOIN replies ON replies.topic_id = topics.id AND replies.is_deleted = 0
             GROUP BY topics.id
             ORDER BY topics.created_at DESC'
        ));
    }

    public function topic(int $id): ?array
    {
        $row = SQLManager::sqlScript(
            'SELECT topics.id, topics.category_id, topics.user_id, topics.title, topics.body, topics.locked,
                    topics.created_at, users.name AS author_name
             FROM topics
             INNER JOIN users ON users.id = topics.user_id
             WHERE topics.id = :id
             LIMIT 1',
            ['id' => $id]
        );

        return $row === [] ? null : $row;
    }

    public function repliesForTopic(int $topicId): array
    {
        return $this->rows(SQLManager::sqlScript(
            'SELECT replies.id, replies.topic_id, replies.user_id, replies.body, replies.created_at,
                    users.name AS author_name
             FROM replies
             INNER JOIN users ON users.id = replies.user_id
             WHERE replies.topic_id = :topic_id AND replies.is_deleted = 0
             ORDER BY replies.created_at ASC',
            ['topic_id' => $topicId]
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

Keep all SQL in repositories or write services. That keeps controllers DRY and gives the project one place to change when persistence changes.

`SQLManager::sqlScript()` returns one associative row when exactly one row matches and a list of rows when multiple rows match. The private `rows()` helper normalizes list queries so templates always receive an array of rows.

## MySQL Note

For MySQL, keep the same repositories and change the database configuration:

```env
DB_TYPE=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=coriander_forum
DB_USER=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

The schema is almost the same, but use MySQL syntax such as `INT AUTO_INCREMENT PRIMARY KEY`, `TINYINT(1)` for booleans, and `DATETIME DEFAULT CURRENT_TIMESTAMP`.

## Checkpoint

Run the migration, open [/forum-demo](/forum-demo), and confirm the page reads categories and topics from SQLite.

## Common Mistakes

- Putting SQL directly inside templates.
- Letting controllers know which database driver is used.
- Building the public-site safety behavior before the real local database works.

## Next

Continue with [Routes](/guided-projects/forum/routes).
