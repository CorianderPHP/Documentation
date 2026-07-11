# Forum Project Structure

This chapter creates the app-owned structure for the forum. It keeps the demo update-safe because no project code is placed inside `CorianderCore`.

## Goal

Create the folders and files that will hold the forum feature before adding behavior.

## Files Created

```structure
src/Routes/forum-demo.php
src/Controllers/ForumDemoController.php
src/ApiControllers/ForumDemoController.php
src/Middleware/ForumDemoAdminMiddleware.php
src/Modules/ForumDemo/Auth/
src/Modules/ForumDemo/Data/
src/Modules/ForumDemo/Permissions/
src/Modules/ForumDemo/Writes/
database/migrations/
public/public_views/forum-demo/
nodejs/src/forum-demo/
```

## Step: Create The Route File

You can use the framework route generator to create the file:

```bash
php coriander make:route forum-demo
```

This creates `src/Routes/forum-demo.php` with a small starter route. The generated file will look like this:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;

return static function (Router $router): void {
    $router->get('forum-demo', static function (ServerRequest $request): Response {
        return new Response(200, [], 'forum-demo route');
    });
};
```

This proves the route file is registered and reachable. In the Routes chapter, you will replace this starter route with the real forum routes.

The route file keeps feature URLs out of `public/routes.php`. The public routes file only includes feature route files.

## Step: Register The Route File

In `public/routes.php`, include the route file.

```php
$forumDemoRoutes = PROJECT_ROOT . '/src/Routes/forum-demo.php';
if (is_file($forumDemoRoutes)) {
    (require $forumDemoRoutes)($router);
}
```

Do this once. After that, every forum URL belongs in `src/Routes/forum-demo.php`.

## Step: Create The Controller

Create `src/Controllers/ForumDemoController.php`.

```php
<?php
declare(strict_types=1);

namespace Controllers;

final class ForumDemoController
{
    public function index(): void
    {
        echo 'Forum demo';
    }
}
```

This placeholder proves that the route and controller can be connected before the feature becomes complex.

## Step: Create Module Folders

Use modules for reusable app logic.

```structure
src/Modules/ForumDemo/Auth
src/Modules/ForumDemo/Data
src/Modules/ForumDemo/Permissions
src/Modules/ForumDemo/Writes
```

These folders map directly to responsibilities:

- `Auth` knows who the visitor is.
- `Data` reads users, categories, topics, and replies from SQLite.
- `Permissions` decides what an account can do.
- `Writes` contains the real SQLite write service and the public documentation demo protection.

## Step: Create View Folders

Create the view root:

```structure
public/public_views/forum-demo/
```

The first page will be `public/public_views/forum-demo/index.php`.

## Checkpoint

Add a temporary GET route for `/forum-demo`, open [/forum-demo](/forum-demo), and confirm the framework router owns the URL.

## Common Mistakes

- Creating one giant `ForumService` too early. Split by responsibility from the start.
- Putting documentation demo logic in official framework modules. This is a custom project module, not a Coriander official module.
- Editing `CorianderCore` to register the route. Use `public/routes.php` instead.

## Next

Continue with [SQLite Data Model](/guided-projects/forum/data-model).
