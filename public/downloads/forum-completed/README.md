# CorianderPHP Forum Completed App Reference

Use this package as a reference implementation for the guided forum project.

This is not a full CorianderPHP project and it does not include the framework. The files are copied from the documentation demo and are meant to be placed inside an existing CorianderPHP application.

The hosted public demo protects visitor writes, but the guide explains where local SQLite persistence belongs when you build the project yourself.

## Install Into An Existing App

Use CorianderPHP v0.2.3.3 or later. Install the framework's Composer and Node dependencies, then copy this package's app-owned folders into that project. In `public/routes.php`, include the route file:

```php
(require PROJECT_ROOT . '/src/Routes/forum-demo.php')($router);
```

Routing is explicit: copying controllers or views does not register URLs. Keep the framework's session bootstrap, CSRF middleware, and request-aware response emitter in `public/index.php`. Build the copied frontend sources with `php coriander nodejs run build-prod`, then open `/forum-demo`.

The JSON forum API shares the demo login session. POST requests need a valid `X-CSRF-Token` header from `Csrf::token()` or the page's hidden CSRF input. The controller explicitly resumes the session and validates that token; API URLs do not get these checks automatically.

This reference package retains read-only demo writes. Follow the included data-model and write-service chapters to build the real SQLite version; do not assume this download stores submitted topics.

## Included Areas

- `src/Routes/forum-demo.php`
- `src/Controllers/ForumDemoController.php`
- `src/ApiControllers/ForumDemoController.php`
- `src/Middleware/ForumDemoAdminMiddleware.php`
- `src/Modules/ForumDemo`
- `public/public_views/forum-demo`
- `nodejs/src/forum-demo`
- `documentation/projects/forum`

## Request Flow

```workflow
Route|Maps URLs to controller actions.
Controller|Prepares view data and redirects after writes.
Module or repository|Owns auth, permissions, demo write safety, and data access.
View or redirect|Renders prepared variables for GET requests or redirects after POST requests.
```

- `src/Routes/forum-demo.php` maps URLs to controller actions.
- `src/Controllers/ForumDemoController.php` prepares view data and redirects after writes.
- `src/Modules/ForumDemo` owns auth, permissions, demo write safety, and data access.
- `public/public_views/forum-demo` renders the variables passed by the controller.
- Permission checks in views are only for UX. Server-side checks still happen in middleware and write services.

## Important

Do not put project code in `CorianderCore`. Keep app behavior in app-owned folders so framework updates can replace the core safely.
