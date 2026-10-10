# Core Concepts

CorianderPHP combines file-based routing, PSR request/response objects, PHP views, middleware, database tools, and frontend build commands. Application code lives outside the replaceable framework core.

## Routes

A method file defines a URL: `src/Routes/about.get.php` serves GET /about. It returns a callable; the callable returns a PSR response. Discovery does not execute every route file.

## Handlers

Handlers read request input, call reusable app logic, and select a response. Keep a tiny endpoint in the route file or delegate to an ordinary `App\Actions` class. The framework does not discover action classes automatically.

## Modules

Custom modules are your own services, repositories, validators, and permission classes. Use `src/Modules` with the starter's `App\` namespace. Official framework modules live in the framework repository; do not put application features in `CorianderCore`.

## Views

Templates live in `src/Views`. A handler returns `Responses::view()`; the renderer includes the nearest `_header.php` and `_footer.php` and supplies prepared data. Views never create routes.

## Middleware

`src/Routes/_middleware.php` declares root protection. Child directories can add gates for admin or API areas. Middleware receives matched parameters before the handler runs.

## Database And Sessions

Migrations describe tables. Repositories use `SQLManager` and parameterized `sqlScript()` queries. Authentication code explicitly starts a session with `SessionBootstrap::start()`; public pages do not need a session.

## A Typical Request

```workflow
Request factory|Builds a bounded, parsed PSR request from the incoming HTTP request.
Router|Discovers the matching method file and path parameters.
Middleware|Checks request gates and can stop with an error response.
Handler|Reads input and calls app modules or repositories.
Response|Returns HTML, a rendered view, JSON, or a redirect.
Emitter|Sends status, headers, and body; suppresses the body for HEAD.
```

Start with [Installation](/documentation/installation), [Routing](/documentation/routing), and [Views](/documentation/views). Existing 0.2.x apps need the [Upgrade Guide](/documentation/upgrades).
