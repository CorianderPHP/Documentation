# Core Concepts

CorianderPHP combines file-based routing, PSR request/response objects, PHP views, middleware, database tools, and frontend build commands. Application code lives outside the replaceable framework core.

## Routes

A method file defines a URL: `src/Views/about.get.php` serves GET /about. A GET file can render PHP/HTML directly, or return a callable producing a PSR response. Other method files must return callables. Discovery does not execute every route file.

## Handlers

Handlers read request input, call reusable app logic, and select a response. Keep a tiny endpoint in the route file or delegate to an ordinary `App\Actions` class. The framework does not discover action classes automatically.

## Modules

Custom modules are your own services, repositories, validators, and permission classes. Use `src/Modules` with the starter's `App\` namespace. Official framework modules live in the framework repository; do not put application features in `CorianderCore`.

## Views

The Views tree holds direct GET pages and unsuffixed templates. Method files create URLs; unsuffixed templates are explicitly rendered with `Responses::view()`. Metadata, independent nearest layouts, and compiled directory/page scripts support rendering. See [View Overview](/documentation/views) and [Page Scripts](/documentation/page-scripts).

## Middleware

`src/Views/_middleware.php` declares root protection. Child directories can add gates for admin or API areas. Middleware receives matched parameters before the handler runs.

## Database And Sessions

Migrations describe tables. Repositories use `SQLManager` and parameterized `sqlScript()` queries. Authentication code explicitly starts a session with `SessionBootstrap::start()`; public pages do not need a session.

## A Typical Request

```workflow
Request factory|Builds a bounded, parsed PSR request from the incoming HTTP request.
Router|Discovers the matching method file and path parameters.
Middleware|Checks request gates and can stop with an error response.
Page or handler|A direct GET renders content; a callable reads input, calls modules, and chooses a response.
Response|Returns HTML, a rendered view, JSON, or a redirect.
Emitter|Sends status, headers, and body; suppresses the body for HEAD.
```

Start with [Installation](/documentation/installation), [Routing](/documentation/routing), and [Views](/documentation/views). Existing 0.3.x and older apps need the [Upgrade Guide](/documentation/upgrades).
