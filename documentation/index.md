# Documentation

Start with what you want to build. This documentation targets **CorianderPHP 0.3.0**. If you already have a 0.2.x application, read the [Upgrade Guide](/documentation/upgrades): this release changes routing, middleware, templates, and bootstrap code.

## Install The Framework

```workflow
Download|Choose a version from the [framework releases](https://github.com/CorianderPHP/CorianderPHP/releases), not this documentation repository.
Extract|Put the release contents in your application folder.
Remove maintenance files|You do not need repository-only .github/, docs/, AGENTS.md, or readme.md to run an app. Keep required license notices when redistributing.
Install dependencies|Run composer install, then php coriander nodejs run install.
Configure and build|Set local environment values and build assets using the [Installation guide](/documentation/installation).
```

## Create A Page

A page needs a **route file** and usually a **view template**. A fixed page does not need an action class.

```bash
php coriander make:route about
php coriander make:view about
```

These create `src/Routes/about.get.php` and `src/Views/about.php`. In the route, return `Responses::view('about', ['title' => 'About'])`.

- [Static View Guide](/documentation/static-views): complete fixed-page example and shared layout.
- [Dynamic View Guide](/documentation/dynamic-views): validated path parameter, prepared data, template, and 404 behavior.
- [Routing](/documentation/routing): filenames, methods, matching, and route inspection.

## Create A Request Handler

A method file returns a callable that accepts the request and returns a PSR response. Small handlers can stay in the file; larger ones can delegate to ordinary app-owned action classes.

- [Request Handlers](/documentation/handlers): read query/form/JSON data, validate it, and return HTML, JSON, views, or redirects.
- [Recommended App Architecture](/documentation/app-architecture): responsibilities and when to extract reusable logic.

There is no controller generator or automatic controller discovery in 0.3.0.

## Create An API

Follow the [Shelter REST API project](/guided-projects/shelter-api) for SQLite persistence, GET/POST/PATCH/DELETE routes, validation, and errors. Its [playground](/guided-projects/shelter-api/playground) accepts demonstration writes without changing stored data.

Use [Request Handlers](/documentation/handlers) for response helpers and parsed bodies. Use [Security](/documentation/security) before exempting a stateless API from CSRF protection.

## Use A Database

```workflow
Schema|Create a migration with php coriander make:migration CreatePostsTable.
Apply|Run php coriander migrate after configuring SQLite or MySQL.
Read and write|Use [SQLManager](/documentation/database) and bound sqlScript() parameters.
Reuse|Keep queries in repositories; follow [Database Patterns](/documentation/database-patterns).
```

The [Forum data-model chapter](/guided-projects/forum/data-model) and [Shelter data-model chapter](/guided-projects/shelter-api/data-model) show complete schemas.

## Add Authentication And Permissions

Authentication identifies a user. Authorization decides what that user may do.

- [Sessions](/documentation/sessions): explicit startup, login state, and flash messages.
- [Middleware](/documentation/middleware): inherited directory protection.
- [Security](/documentation/security): CSRF tokens, cookies, request limits, and headers.
- [Forum guided project](/guided-projects/forum): shared permission rules for web pages, admin actions, and JSON endpoints.

## Reference And Operational Guides

- [CLI](/documentation/cli): generators, detailed help, routes:list, migrations, and updates.
- [Core Concepts](/documentation/concepts) and [Request Lifecycle](/documentation/request-lifecycle): how the pieces fit together.
- [View Overview](/documentation/views) and [Assets And Images](/documentation/assets): templates, layouts, escaping, and public files.
- [Modules](/documentation/modules): app-owned services and repositories.
- [Cache](/documentation/cache): automatic route-map refresh.
- [Sitemap](/documentation/sitemap): explicit public URLs.
- [NodeJS Integration](/documentation/nodejs): Tailwind and TypeScript builds.
- [Environment](/documentation/environment): local and production configuration.
- [Production Checklist](/documentation/production): safe hosting and deployment.
- [Errors And Debugging](/documentation/debugging): identify the failing layer.
- [Testing An App](/documentation/testing): route, permission, repository, and download regressions.
- [Upgrade Guide](/documentation/upgrades): migrate app-owned code without editing CorianderCore.

## Framework Boundary

Keep application code under `src`, migrations under `database`, and public assets under `public/assets`. Do not edit `CorianderCore` or the `coriander` CLI; updates manage those files.
