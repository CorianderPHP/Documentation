# Recommended App Architecture

Keep framework files separate from application behavior. In 0.4.1, pages and HTTP handlers share one private Views tree; classes and browser helpers remain ordinary app-owned code.

```structure
CorianderCore/                 # Framework-managed
coriander                      # Framework-managed CLI
src/
  Views/                       # Method files, templates, middleware declarations
    _metadata.php
    _header.php
    _footer.php
    _middleware.php
    index.get.php
  Actions/                     # Optional request-coordination classes
  Middleware/                  # App-owned PSR-15 request gates
  Modules/                     # Repositories, services, validation, permissions
  Scripts/                     # Shared imported TypeScript
database/migrations/
public/
  index.php                    # Application bootstrap
  assets/                      # Public CSS, JS, images, fonts
tests/
```

`Actions` is a convention, not a special framework feature. A fixed page can be direct HTML in a GET file; a small JSON/write endpoint can keep its callable in its method file. The starter's `"App\\": "src/"` mapping autoloads application classes.

## Responsibility Map

These are responsibilities, not a workflow every class must perform:

```responsibilities
Pages and handlers|Own the HTTP boundary.|Direct GET pages render simple content; callable handlers validate request input, call modules, and choose responses.
Modules|Own reusable app logic.|Repositories; business workflows; validators; permission rules.
Middleware|Own request gates.|Authentication; admin access; request limits; shared response headers.
Templates and layouts|Own presentation.|HTML; prepared data; forms; small display conditions; metadata presentation.
```

The shared directory does not mean every file should do everything. Keep database queries and reusable permission decisions in modules. A complex handler can prepare data and render an unsuffixed template; that template should not query SQL.

## Start Small

Use a [direct GET page](/documentation/static-views) for fixed content. Use a callable and `Responses::json()` for an API. Extract an action class when several endpoints share dependencies. Extract a module when business logic repeats or deserves independent tests.

An article handler can ask an `ArticleRepository` for a record, return 404 when missing, and render `articles/show.php`. The repository owns SQL, the handler owns response decisions, and the template owns HTML.

## SOLID And DRY In Practice

- Keep one reason to change per class: queries in repositories, ability rules in a permission service.
- Reuse a write service from web and API handlers instead of duplicating authorization or SQL.
- Inject a dependency when another implementation or a test double is needed; avoid interfaces that add no useful boundary.
- Keep CSRF and authentication in inherited `_middleware.php` declarations, not copied into endpoints.
- Use metadata for layout defaults and ordinary imports for shared browser code.
- Return consistent response/result shapes instead of repeating error formatting.

The Container and controller discovery were removed in 0.3.0. Constructors and app factories remain normal PHP code, not framework registration.

## Request Boundary And Persistence

Validate request values before use. Direct-page local values and request attributes are raw; encode them for their output context. Explicit view string-array data and metadata are already HTML-escaped.

Use bound SQL parameters, migrations for schema changes, and explicit session startup before authentication reads/writes. See [Request Lifecycle](/documentation/request-lifecycle), [Database Patterns](/documentation/database-patterns), and the guided projects.
