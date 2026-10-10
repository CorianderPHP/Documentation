# Routing Module Guide

CorianderPHP's routing system maps incoming HTTP requests to callbacks, controllers, or views. Since v0.2.3.3, routing is explicit by default: a controller method or view folder is not reachable until you register its URL.

Custom route definitions start in `public/routes.php`. For small projects such as SPAs, marketing websites, brochure websites, and simple landing pages, keeping routes in this single file is usually the clearest option.

For larger applications, split route groups into app-owned files under `src/Routes/` and include them from `public/routes.php`.

## Small Project Routes

Register a homepage in `public/routes.php`, including `/` itself:

```php
use CorianderCore\Core\Router\ViewRenderer;

$router->get('/', static fn () => (new ViewRenderer())->render('home'));
```

This renders `public/public_views/home/index.php`. Use the same pattern for [static views](/documentation/static-views), or delegate to a [controller](/documentation/controllers) when a page needs prepared data.

Custom routes are defined in `public/routes.php`. The front controller bootstraps the router and passes an instance to this file, so you can add routes directly:

```php
use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\ServerRequest;

/** @var Router $router */

$router->get('/hello/{name}', function (ServerRequest $request) {
    $name = $request->getAttribute('name');
    return new \Nyholm\Psr7\Response(200, [], "Hello {$name}");
});

$router->setNotFound(fn() => new \Nyholm\Psr7\Response(404, [], 'Not Found'));
```

The router also provides `post()`, `put()`, `patch()`, and `delete()` shortcuts.
Use `add($method, ...)` only when the method is dynamic or uncommon.

## Larger Project Route Files

Use `src/Routes/` when `public/routes.php` becomes too large or when routes naturally split by feature area, such as admin, shop, account, or API-like web endpoints.

Create a route file with:

```bash
php coriander make:route admin
```

This creates:

```structure
src/Routes/admin.php
```

The generated file returns a closure that receives the router:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;

return static function (Router $router): void {
    $router->get('admin', static function (ServerRequest $request): Response {
        return new Response(200, [], 'admin route');
    });
};
```

Register it from `public/routes.php`:

```php
$adminRoutes = PROJECT_ROOT . '/src/Routes/admin.php';
if (is_file($adminRoutes)) {
    (require $adminRoutes)($router);
}
```

Nested route files are also supported:

```bash
php coriander make:route admin/users
```

This creates `src/Routes/admin/users.php`.

### Route Groups

Group routes to share a common URI prefix or middleware:

```php
use Psr\Http\Server\MiddlewareInterface;

$auth = new class implements MiddlewareInterface {
    public function process($request, $handler) {
        // authentication logic
        return $handler->handle($request);
    }
};

$router->group('/admin', [$auth], function (Router $r) {
    $r->get('/dashboard', fn (ServerRequest $req) =>
        new \Nyholm\Psr7\Response(200, [], 'Dashboard'));
});
```

Routes inside the group inherit the `/admin` prefix and the `$auth` middleware.

### Per-route Middleware

Middleware can also be attached directly when registering a route:

```php
$router->get('/profile', fn (ServerRequest $r) =>
    new \Nyholm\Psr7\Response(200, [], 'Profile'), [$auth]);
```

### Response Handling

- Route callbacks and controller actions can return a `ResponseInterface`; status code, headers, and body are preserved.
- Explicit API routes must return a JSON response, not a plain array. Set both the HTTP status and `Content-Type`.
- Text emitted by a view is captured as the response body.

```php
use Nyholm\Psr7\Response;

$router->get('/api/health', static fn () => new Response(
    200,
    ['Content-Type' => 'application/json; charset=utf-8'],
    json_encode(['ok' => true], JSON_THROW_ON_ERROR)
));
```

An API controller is an ordinary PHP class called by your route. Its folder and method names do not register endpoints.

## GET And HEAD

A registered GET route also handles HEAD unless you register a dedicated HEAD route with `$router->add('HEAD', ...)`. HEAD fallback retains route parameters and middleware. Keep GET actions read-only because HEAD can execute the same callback.

In `public/index.php`, pass the request method to the emitter so HEAD responses contain headers but no body:

```php
$request = RequestFactory::fromGlobals();
$response = $router->dispatch($request);
ResponseEmitter::emit($response, $request->getMethod());
```

Import `CorianderCore\Core\Http\RequestFactory` and `CorianderCore\Core\Http\ResponseEmitter`. See the [upgrade checklist](/documentation/upgrades) when migrating an existing entry point.

## Legacy Automatic Routing

Older apps can opt in when constructing the router:

```php
use CorianderCore\Core\Router\Router;

$router = new Router(automaticRouting: true);
```

This re-enables controller, API, and view discovery. Prefer explicit routes for new apps: a convention-generated alias may reach an action outside the middleware protecting its registered route. In legacy API discovery only, returned arrays are encoded as JSON automatically.

Automatic web actions are method-restricted: read actions accept GET/HEAD, `store` accepts POST, `update` accepts POST/PUT/PATCH, and `delete`/`destroy` accept POST/DELETE. A custom action can declare its allowed methods:

```php
use CorianderCore\Core\Router\HttpMethods;

#[HttpMethods('POST')]
public function publish(): Response
{
    return new Response(200, [], 'Published');
}
```

The attribute applies to legacy discovery; explicit routes declare their method through `get()`, `post()`, or `add()`.

## Error Handling

- Register a `setNotFound` callback to handle unmatched routes gracefully.
- Wrap route logic in `try/catch` blocks to log and report exceptions without exposing sensitive data:

```php
$router->post('/user', function(ServerRequest $request) {
    try {
        // process request
    } catch (\Throwable $e) {
        // log and return 500 response
    }
});
```

## Best Practices

- Group related routes into separate files and include them during bootstrap to keep definitions maintainable.
- Keep `public/routes.php` for small custom route lists and for including route files from `src/Routes/`.
- Leverage PSR-15 middleware for cross-cutting concerns such as authentication or CSRF protection on mutating methods (`POST`, `PUT`, `PATCH`, `DELETE`).
- Use URL parameters instead of query strings for cleaner, cache-friendly routes.
- Register every public URL explicitly, including `/` and API endpoints. Attach authorization middleware to all protected entry points.
