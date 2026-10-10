# Middleware

Middleware runs before and after a selected route handler. Use it for request gates such as authentication, authorization, request limits, and response headers.

In 0.3.0, middleware is declared in route-directory `_middleware.php` files, not router groups.

## Protect A Directory

Create an app-owned `src/Middleware/AdminMiddleware.php`:

```php
<?php
declare(strict_types=1);

namespace App\Middleware;

use CorianderCore\Core\Bootstrap\SessionBootstrap;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AdminMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        SessionBootstrap::start();
        if (($_SESSION['role'] ?? null) !== 'admin') {
            return Responses::html('Forbidden', 403);
        }

        return $handler->handle($request);
    }
}
```

This minimal example checks a session role. For a real app, resolve the authenticated user and delegate to your permission service, as the [forum project](/guided-projects/forum/admin-area) does. Never trust a submitted role.

Add `src/Routes/admin/_middleware.php`:

```php
<?php
use App\Middleware\AdminMiddleware;

return [new AdminMiddleware()];
```

Now every handler under `src/Routes/admin` inherits this gate. An `admin/users/index.get.php` handler cannot be reached through an unprotected controller alias because there is no controller discovery.

## Order And Inheritance

```structure
src/Routes/
  _middleware.php
  index.get.php
  admin/
    _middleware.php
    index.get.php
    users/index.get.php
```

For `/admin/users`, middleware runs from root to admin, then the handler. Responses return through the chain in reverse order. Child middleware adds protection; it does not replace or bypass the parent list.

Matched parameters are available through `getAttribute()` before middleware executes. Root middleware also wraps missing-route responses. Matching-directory middleware wraps unsupported-method responses.

A middleware may stop the chain by returning a response without calling `$handler->handle($request)`.

## Root Security Policy

The starter's `src/Routes/_middleware.php` returns framework middleware:

```php
<?php
use CorianderCore\Core\Security\ApiRequestLimitsMiddleware;
use CorianderCore\Core\Security\CsrfMiddleware;
use CorianderCore\Core\Security\SecurityHeadersMiddleware;

return [
    new SecurityHeadersMiddleware(),
    new ApiRequestLimitsMiddleware(apiPrefixes: []),
    new CsrfMiddleware(),
];
```

An empty prefix list on request limits applies them to all routes. CSRF protects mutating methods including `/api`; there is no default API exemption. See [Security](/documentation/security) before making a narrowly scoped exception for a stateless API.

Keep root protection when adding child middleware. Returning `[]` in a child file does not disable root CSRF.
