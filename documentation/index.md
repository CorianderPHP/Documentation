# Documentation

Start from what you want to build. Each path points to the exact framework pieces you need, without forcing you to read every reference page first.

## I Want To Create A Page

A normal page usually needs a route, a controller action, and a view.

If you are deciding where files should live, start with [Recommended App Architecture](/documentation/app-architecture).

1. Use [Routing](/documentation/routing) to understand how URLs reach your code.
2. Use [Controllers](/documentation/controllers) to create the request handler.
3. Use [Views](/documentation/views) to render the HTML.
4. Use [Security](/documentation/security) when the page contains a form.

Useful command:

```bash
php coriander make:controller Blog
```

## I Want To Create A Controller

Controllers should stay thin. They read the request, call app-owned modules or repositories, and return a response or render a view.

1. Read [Controllers](/documentation/controllers).
2. Read [Views](/documentation/views) if the controller returns HTML.
3. Read [Routing](/documentation/routing) if you need custom URLs.

For API endpoints, generate an API controller:

```bash
php coriander make:controller Shelter --api
```

## I Want To Create An API

An API usually needs route files, API controllers, validation, JSON responses, and database access.

1. Start with the [Shelter REST API guided project](/guided-projects/shelter-api).
2. Use [Routing](/documentation/routing) for API route files.
3. Use [Controllers](/documentation/controllers) for API controller structure.
4. Use [Database](/documentation/database) when the API reads or writes persistent data.

The guided project includes a playground so you can test GET, POST, PATCH, and DELETE behavior without changing a real database.

## I Want To Use A Database

Database work usually starts with a migration, then moves into repositories or modules that call `SQLManager`.

1. Use [Database](/documentation/database) for connection, migrations, and `SQLManager`.
2. Use [Database Patterns](/documentation/database-patterns) to decide between helpers, `sqlScript()`, repositories, SQLite, and MySQL.
3. Use [Modules](/documentation/modules) to keep repository code out of controllers.
4. Use [Routing](/documentation/routing) and [Controllers](/documentation/controllers) to expose the data through pages or APIs.

Useful commands:

```bash
php coriander make:migration create_posts_table
php coriander migrate
```

For larger SQL queries, prefer `sqlScript()` so the query stays readable and reusable.

## I Want Authentication Or Permissions

Authentication tells the app who the user is. Permissions decide what that user may do.

1. Use [Middleware](/documentation/middleware) to protect route groups.
2. Use [Security](/documentation/security) for CSRF and form safety.
3. Follow the [Forum permissions guided project](/guided-projects/forum) for a complete web example.

The forum project shows guests, members, and admins using the same permission rules from views, controllers, middleware, write services, and API endpoints.

## I Want To Organize Reusable Code

Use custom modules for app-owned logic that should not live in controllers or `CorianderCore`.

1. Read [Recommended App Architecture](/documentation/app-architecture).
2. Read [Modules](/documentation/modules).
3. Use [Request Lifecycle](/documentation/request-lifecycle) to understand where middleware, controllers, modules, and views run.
4. Put app-specific services, repositories, and permission classes under `src/Modules`.
5. Keep official framework code inside `CorianderCore` untouched.

Recommended shape:

```structure
src/
  Modules/
    Blog/
      BlogRepository.php
      BlogService.php
```

## For Experienced Users

Use this section when you already know the feature you need and want the reference page directly.

- [Recommended App Architecture](/documentation/app-architecture): where controllers, modules, repositories, middleware, views, validation, and permissions belong.
- [Request Lifecycle](/documentation/request-lifecycle): how requests move through `public/index.php`, routes, middleware, controllers, modules, and responses.
- [Database Patterns](/documentation/database-patterns): when to use migrations, `SQLManager`, `sqlScript()`, repositories, SQLite, and MySQL.
- [Production Checklist](/documentation/production): environment, hosting, HTTPS, proxies, database, logs, assets, and final release checks.
- [Errors And Debugging](/documentation/debugging): common 404, 405, asset, environment, database, CSRF, and hosting issues.
- [Testing An App](/documentation/testing): route smoke tests, module tests, repository tests, permission tests, and documentation quality checks.
- [Upgrade Guide](/documentation/upgrades): how to update the framework without mixing app behavior into `CorianderCore`.
- [CLI](/documentation/cli): scaffolding commands, maintenance commands, and framework updates.
- [Routing](/documentation/routing): route files, groups, middleware, response handling, and not-found behavior.
- [Controllers](/documentation/controllers): web controllers, API controllers, rendering, and action structure.
- [Middleware](/documentation/middleware): PSR-15 middleware and route-group protection.
- [Views](/documentation/views): public views, view data, escaping, and forms.
- [Database](/documentation/database): migrations, SQLite/MySQL configuration, `SQLManager`, and `sqlScript()`.
- [Modules](/documentation/modules): app-owned reusable logic outside controllers.
- [Security](/documentation/security): CSRF, headers, trusted proxies, and request safety.
- [Cache](/documentation/cache): cache behavior and invalidation.
- [Sitemap](/documentation/sitemap): sitemap metadata and public pages.
- [NodeJS Integration](/documentation/nodejs): Tailwind, TypeScript, builds, and frontend assets.

## Guided Projects

- [Build a Forum with User Permissions](/guided-projects/forum): full web app with SQLite, authentication, permissions, admin middleware, write services, API endpoints, and public-demo protection.
- [Build a Shelter REST API](/guided-projects/shelter-api): full JSON API with filtering, validation, database access, consistent errors, and a request playground.

## Framework Update Rule

Do not put app or documentation behavior inside `CorianderCore`. Framework updates can replace that folder. Keep app-owned code in `src`, `public/public_views`, `documentation`, `database`, `resources`, and `nodejs`.
