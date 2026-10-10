# File-Based Routing

CorianderPHP 0.3.0 discovers routes from method files in `src/Routes`. The folder path describes the URL; the filename suffix describes the HTTP method. You do not include route files in `public/routes.php` or register them with `$router->get()`.

## Create Your First Route

From the project root, you can run:

```bash
php coriander make:route hello
php coriander routes:list
```

The first command creates `src/Routes/hello.get.php`. Replace its contents with:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    return Responses::html('<h1>Hello</h1>');
};
```

Open `/hello`. The file returns a callable; that callable receives the request and **returns a PSR response**. Do not echo HTML or return a plain string. Use `Responses::view()` for templates or `Responses::json()` for JSON.

Creating these files by hand is also supported. Generators are conveniences, not a registration step.

## Paths And Methods

| File | Request |
| --- | --- |
| `index.get.php` | `GET /` |
| `about.get.php` | `GET /about` |
| `admin/index.get.php` | `GET /admin` |
| `articles/[id].get.php` | `GET /articles/42` |
| `articles/[id].patch.php` | `PATCH /articles/42` |
| `teams/[team]/users/[id].get.php` | `GET /teams/5/users/42` |

Supported lowercase suffixes are `get`, `post`, `put`, `patch`, `delete`, `head`, and `options`. `index` means the directory's own URL. For a POST handler:

```bash
php coriander make:route "articles/[id].post"
```

A dynamic segment such as `[id]` matches a segment, not a validated database id. Read and validate it before using it:

```php
<?php
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    $id = (string) $request->getAttribute('id');
    if (!ctype_digit($id) || (int) $id < 1) {
        return Responses::json(['error' => 'Invalid article id.'], 404);
    }

    return Responses::json(['id' => (int) $id]);
};
```

Regex, optional parameters, and catch-all segments from earlier router APIs are not supported. Put constraints in middleware or the handler.

## Read The Request

- `getAttribute('id')`: matched path parameter.
- `getQueryParams()`: query values such as `?page=2`.
- `getParsedBody()`: parsed form or JSON data.
- `getUploadedFiles()`: PSR uploaded-file objects, including nested fields.
- `getCookieParams()` and `getHeaderLine('Content-Type')`: cookies and headers.

These methods are available on the request created by the starter's `RequestFactory`. See [Request Handlers](/documentation/handlers) for validation examples.

## Middleware And Visibility

Place `_middleware.php` in the route directory you want to protect. Root middleware runs first; child directories add their own middleware. Children cannot remove a parent's protection. See [Middleware](/documentation/middleware).

Files and directories whose names start with `_` or `.` are private. Reusable classes belong in `src/Actions` or `src/Modules`, not disguised as routable PHP files. Views never create URLs.

## Matching And Errors

Static paths win before method selection. If `articles/new.get.php` and `articles/[id].post.php` exist, `POST /articles/new` returns 405 rather than treating `new` as an id.

- No matching path: 404.
- Matching path, unsupported method: 405 with an `Allow` header.
- GET supplies a HEAD fallback unless an explicit `.head.php` exists.
- The response emitter suppresses all HEAD bodies, including errors.
- OPTIONS needs an explicit handler.
- Paths are case-sensitive; trailing slashes are accepted.
- Duplicate definitions and conflicting dynamic parameter names are errors.

Discovery reads filenames without executing handlers. Only the selected handler loads after middleware permits it.

## Inspect And Cache Routes

```bash
php coriander routes:list
```

This lists URLs, methods, handler files, HEAD fallbacks, and inherited middleware without executing app code. The route map [refreshes automatically](/documentation/cache); there is no controller-cache build step.

For a custom bootstrap, use `(new Router())->handle($request)` and emit the response with the original request method. See [Request Lifecycle](/documentation/request-lifecycle).

Upgrading from 0.2.x? Follow the [0.3.0 migration checklist](/documentation/upgrades) before replacing your old route registration.
