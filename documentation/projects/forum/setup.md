# Forum Project Structure

Start from an installed **CorianderPHP 0.3.0** app; see [Installation](/documentation/installation). This chapter establishes app-owned folders before adding SQLite, permissions, and forms.

## Create The First Route

You can run:

```bash
php coriander make:route forum-demo
```

This creates `src/Routes/forum-demo.get.php`. Replace its contents with a temporary response:

```php
<?php
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    Responses::html('<h1>Forum setup works</h1>');
```

Visit `/forum-demo` and check `php coriander routes:list`. Discovery is automatic: do not add an include to `public/routes.php`.

For the finished feature we will move this root handler to `forum-demo/index.get.php`. **Remove the temporary `forum-demo.get.php` when doing that**: both represent the same GET URL and would conflict.

## Prepare The App Structure

```structure
src/
  Routes/
    forum-demo/
    api/forum-demo/
  Actions/
    ForumActions.php
    ForumApiActions.php
  Middleware/
    ForumDemoAdminMiddleware.php
  Modules/ForumDemo/
    Auth/
    Data/
    Permissions/
    Writes/
  Views/
    _header.php
    _footer.php
    forum-demo.php
    forum-demo/
database/migrations/
nodejs/src/forum-demo/
```

Create action and module classes as normal PHP files. There is no action/controller generator in 0.3.0. With the starter's `"App\\": "src/"` Composer mapping, `src/Actions/ForumActions.php` uses namespace `App\Actions`.

- `Routes`: one method file per URL/method, plus inherited middleware.
- `Actions`: request coordination, response selection, and redirects.
- `Auth`: current user from the session.
- `Data`: SQLite reads.
- `Permissions`: the shared ability matrix.
- `Writes`: real local writes and the optional public-demo guard.
- `Views`: HTML only; templates do not expose endpoints.

Run `composer dump-autoload` if you changed Composer mappings.

## Configure SQLite

```env
DB_TYPE=sqlite
DB_NAME=database/forum.sqlite
```

Ensure the `database` directory exists and is writable locally. The next chapter creates and seeds tables through a migration.

Keep root security middleware from the starter. We will add an admin gate under the forum routes; do not disable CSRF to make future forms work.

## Checkpoint

Your temporary GET /forum-demo responds and all new files are outside CorianderCore. Next, create the [SQLite Data Model](/guided-projects/forum/data-model). No login or persistence is expected yet.
