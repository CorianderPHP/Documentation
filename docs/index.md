# Documentation

Start from what you want to build. Each path points to the exact framework pieces you need, without forcing you to read every reference page first.

## I Want To Create A Page

A normal page usually needs a route, a controller action, and a view.

1. Use [Routing](/docs/routing) to understand how URLs reach your code.
2. Use [Controllers](/docs/controllers) to create the request handler.
3. Use [Views](/docs/views) to render the HTML.
4. Use [Security](/docs/security) when the page contains a form.

Useful command:

```bash
php coriander make:controller Blog
```

## I Want To Create A Controller

Controllers should stay thin. They read the request, call app-owned modules or repositories, and return a response or render a view.

1. Read [Controllers](/docs/controllers).
2. Read [Views](/docs/views) if the controller returns HTML.
3. Read [Routing](/docs/routing) if you need custom URLs.

For API endpoints, generate an API controller:

```bash
php coriander make:controller Shelter --api
```

## I Want To Create An API

An API usually needs route files, API controllers, validation, JSON responses, and database access.

1. Start with the [Shelter REST API guided project](/guided-projects/shelter-api).
2. Use [Routing](/docs/routing) for API route files.
3. Use [Controllers](/docs/controllers) for API controller structure.
4. Use [Database](/docs/database) when the API reads or writes persistent data.

The guided project includes a playground so you can test GET, POST, PATCH, and DELETE behavior without changing a real database.

## I Want To Use A Database

Database work usually starts with a migration, then moves into repositories or modules that call `SQLManager`.

1. Use [Database](/docs/database) for connection, migrations, and `SQLManager`.
2. Use [Modules](/docs/modules) to keep repository code out of controllers.
3. Use [Routing](/docs/routing) and [Controllers](/docs/controllers) to expose the data through pages or APIs.

Useful commands:

```bash
php coriander make:migration create_posts_table
php coriander migrate
```

For larger SQL queries, prefer `sqlScript()` so the query stays readable and reusable.

## I Want Authentication Or Permissions

Authentication tells the app who the user is. Permissions decide what that user may do.

1. Use [Middleware](/docs/middleware) to protect route groups.
2. Use [Security](/docs/security) for CSRF and form safety.
3. Follow the [Forum permissions guided project](/guided-projects/forum) for a complete web example.

The forum project shows guests, members, and admins using the same permission rules from views, controllers, middleware, write services, and API endpoints.

## I Want To Organize Reusable Code

Use custom modules for app-owned logic that should not live in controllers or `CorianderCore`.

1. Read [Modules](/docs/modules).
2. Put app-specific services, repositories, and permission classes under `src/Modules`.
3. Keep official framework code inside `CorianderCore` untouched.

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

- [CLI](/docs/cli): scaffolding commands, maintenance commands, and framework updates.
- [Routing](/docs/routing): route files, groups, middleware, response handling, and not-found behavior.
- [Controllers](/docs/controllers): web controllers, API controllers, rendering, and action structure.
- [Middleware](/docs/middleware): PSR-15 middleware and route-group protection.
- [Views](/docs/views): public views, view data, escaping, and forms.
- [Database](/docs/database): migrations, SQLite/MySQL configuration, `SQLManager`, and `sqlScript()`.
- [Modules](/docs/modules): app-owned reusable logic outside controllers.
- [Security](/docs/security): CSRF, headers, trusted proxies, and request safety.
- [Cache](/docs/cache): cache behavior and invalidation.
- [Sitemap](/docs/sitemap): sitemap metadata and public pages.
- [NodeJS Integration](/docs/nodejs): Tailwind, TypeScript, builds, and frontend assets.

## Guided Projects

- [Build a Forum with User Permissions](/guided-projects/forum): full web app with SQLite, authentication, permissions, admin middleware, write services, API endpoints, and public-demo protection.
- [Build a Shelter REST API](/guided-projects/shelter-api): full JSON API with filtering, validation, database access, consistent errors, and a request playground.

## Framework Update Rule

Do not put app or documentation behavior inside `CorianderCore`. Framework updates can replace that folder. Keep app-owned code in `src`, `public/public_views`, `docs`, `database`, `resources`, and `nodejs`.
