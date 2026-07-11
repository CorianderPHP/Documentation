# MySQL And Production

The guided project starts with SQLite because it is simple to run locally. The same structure can move to MySQL when the app needs a server database, backups, multiple environments, or stronger operational tooling.

## Goal

Move from local SQLite to a production-ready database without rewriting routes, controllers, views, or permission names.

## What Stays The Same

- Route structure
- Controller action names
- View structure
- Repository public methods
- Permission ability names
- Admin middleware
- API endpoint names

Keeping these stable is the point of the earlier SOLID boundaries.

## What Changes

- Database configuration points to MySQL.
- Migration SQL uses MySQL column syntax.
- Demo accounts become managed users.
- Public demo read-only mode stays enabled only on the official documentation deployment.
- Production writes always persist after validation and permission checks.

## Step: Configure MySQL

```env
DB_TYPE=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=coriander_forum
DB_USER=forum_user
DB_PASSWORD=change-me
DB_CHARSET=utf8mb4
```

Create the database and user outside the app, then run:

```bash
php coriander migrate
```

## Step: Adjust Migration Syntax

SQLite:

```sql
id INTEGER PRIMARY KEY AUTOINCREMENT
locked INTEGER NOT NULL DEFAULT 0
created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
```

MySQL:

```sql
id INT AUTO_INCREMENT PRIMARY KEY
locked TINYINT(1) NOT NULL DEFAULT 0
created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
```

Keep table names and column names the same so repositories barely change.

## Step: Keep Writes In Services

Create one service per write action:

```structure
src/Modules/ForumDemo/Writes/ForumWriteService.php
src/Modules/ForumDemo/Writes/ModerationService.php
src/Modules/ForumDemo/Writes/UserRoleService.php
```

Each service should:

- validate input
- check permissions
- write to the database with `SQLManager::sqlScript()` or the simple SQL helpers
- return a predictable result array or object

Keep custom joins and writes inside repositories or services. Controllers should not contain SQL when moving from SQLite to MySQL.

## Step: Add Production Rules

A real public forum needs more than role checks:

- hashed passwords and account recovery
- locked topics
- deleted replies with audit records
- rate limits for repeated posting
- moderation queues for new accounts
- backups and migration rollback testing

## Checkpoint

After switching to MySQL, open the same URLs:

- [/forum-demo](/forum-demo)
- [/forum-demo/topics](/forum-demo/topics)
- `/forum/topics` in your real app

The app should feel the same from the route, controller, and view perspective.

## Common Mistakes

- Rewriting routes and views when only persistence changed.
- Letting SQL details leak into controllers.
- Disabling public demo protection on the official documentation site.

## Next

You now have the full shape of a database-backed forum and a protected live documentation demo.
