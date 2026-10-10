# Controller Guide

Controllers handle HTTP requests and live under `src/Controllers` (or `src/ApiControllers` for API endpoints).

## Creating a Controller

Generate a controller with the CLI:

```bash
php coriander make:controller Blog
# or create an API controller
php coriander make:controller Blog --api
```

The web command creates `src/Controllers/BlogController.php`. The API command creates `src/ApiControllers/BlogController.php`.

Generated web controllers include a `ViewRenderer` instance. Use it when the controller should render a server-side view from `public/public_views`.

Example with prepared data:

```php
<?php
declare(strict_types=1);

namespace Controllers;

use CorianderCore\Core\Router\ViewRenderer;

final class BlogController
{
    private ViewRenderer $view;

    public function __construct()
    {
        $this->view = new ViewRenderer();
    }

    public function show(string $id): void
    {
        $post = [
            'id' => (int) $id,
            'title' => 'First post',
        ];

        $this->view->render('blog/show', [
            'post' => $post,
        ]);
    }
}
```

The array keys passed to `render()` become variables in the view. In this example, the view receives `$post`.

When the data comes from a database, move that lookup into a repository under `src/Modules`. The controller should call that repository instead of building SQL directly.

## Register The Action

Generating a controller does not expose it over HTTP. Add this route to `public/routes.php`:

```php
use Controllers\BlogController;
use Psr\Http\Message\ServerRequestInterface;

$router->get('/blog/{id:[0-9]+}', static fn (ServerRequestInterface $request) =>
    (new BlogController())->show((string) $request->getAttribute('id'))
);
```

Opening `/blog/42` now calls `show('42')`. For a redirect, error, or JSON endpoint, return a PSR-7 response such as `Nyholm\Psr7\Response`; the router preserves its status, headers, and body. Do not return a plain array from an explicit API route. See [Routing](/documentation/routing) for a JSON response example.

## Best Practices

- Keep actions small and delegate business logic to separate classes.
- Name controllers in PascalCase; the CLI appends `Controller` automatically.
- Place API controllers under `src/ApiControllers` and return JSON responses.
- Validate input and use CSRF guards for state-changing requests (`POST`, `PUT`, `PATCH`, `DELETE`).
