# Recommended App Architecture

Keep framework files separate from application behavior. This structure works for a small site and can grow into the guided projects:

```structure
CorianderCore/                 # Framework-managed
coriander                      # Framework-managed CLI
src/
  Routes/                      # Discovered method files and _middleware.php
  Actions/                     # Optional ordinary request-coordination classes
  Middleware/                  # App-owned PSR-15 request gates
  Modules/                     # Repositories, services, validation, permissions
  Views/                       # Private templates and inherited layouts
database/migrations/
public/
  index.php                    # Application bootstrap
  assets/                      # Browser-accessible assets
tests/
```

`Actions` is a convention, not a required framework feature. A small handler can stay in its route file. The starter's Composer mapping `"App\\": "src/"` autoloads these ordinary classes.

## Responsibility Map

These are separate responsibilities, not steps each class must perform:

```responsibilities
Routes and handlers|Own HTTP request flow.|Read request data; call app services; choose HTML or JSON; redirect after successful writes.
Modules|Own reusable app logic.|Repositories; business workflows; validators; permission rules.
Middleware|Own request gates.|Authentication; admin access; request limits; shared response headers.
Views|Own rendering.|HTML structure; prepared data; forms; small display conditions.
```

Views should not query the database or decide permission rules. Prepare data before rendering; enforce authorization in middleware/services even when a view hides controls.

## Start Small

Create a route and return `Responses::view()` or `Responses::json()`. Extract an action class when several routes share dependencies. Extract a module when logic repeats, needs independent tests, or hides the request flow.

For example, an article detail handler asks an `ArticleRepository` for a record and passes that record to `src/Views/articles/show.php`. The repository owns SQL; the handler owns 404 behavior; the view owns HTML.

## SOLID And DRY In Practice

- Keep one reason to change per class: database queries in repositories, permission decisions in a permission service.
- Reuse the same write service from web and API handlers instead of duplicating SQL or authorization.
- Inject a dependency when you need another implementation or test double. Do not add an interface just to wrap one class.
- Keep CSRF and request gates in inherited middleware, not copied into every route.
- Return one consistent response/result shape rather than repeating error formatting.

Framework 0.3.0 removes the old Container and controller discovery. Your action classes can still use constructors and factories; they are ordinary application code.

## Request Boundary And Persistence

Read query/body values from the request and validate their types. Use bound SQL parameters, migrations for schema changes, and explicit session startup for authentication.

Follow [Request Lifecycle](/documentation/request-lifecycle), [Request Handlers](/documentation/handlers), and [Database Patterns](/documentation/database-patterns). The [Forum](/guided-projects/forum) and [Shelter API](/guided-projects/shelter-api) show these boundaries in larger features.

Never edit `CorianderCore` to add app behavior. Report a framework defect in the [framework issue tracker](https://github.com/CorianderPHP/CorianderPHP/issues).
