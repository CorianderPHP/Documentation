# Framework Concepts

This page explains the moving pieces in a CorianderPHP application before you open the detailed reference pages.

## Routes

Routes map URLs to code. Small apps can define routes directly in `public/routes.php`, but feature areas should use app-owned route files in `src/Routes`.

```structure
src/Routes/dashboard.php
src/Routes/forum-demo.php
```

Keep route files focused on URL shape, HTTP methods, route groups, and middleware attachment.

## Controllers

Controllers coordinate requests. They receive input, call modules or repositories, and render a view or return a response.

Good controller actions are usually short:

- read request data
- call app logic
- render a view
- redirect or return an error response when needed

## Views

Views live in `public/public_views`. They render prepared data. They should not contain database queries, permission rules, or large business decisions.

Escape output in views because a demo can become a real app later.

## Custom App Modules

Custom app modules live in `src/Modules`. Use them for project-specific logic:

```structure
src/Modules/Dashboard/DashboardSummary.php
src/Modules/ForumDemo/Permissions/DemoPermissionService.php
```

These are different from official Coriander modules distributed with the framework. App modules are yours and should stay outside `CorianderCore`.

## Middleware

Middleware runs around a request. Use it when a rule applies to a whole route or group of routes.

Common middleware uses:

- admin-only areas
- request limits
- authentication gates
- headers or security policy

## API Controllers

API controllers live in `src/ApiControllers`. They are separate from web controllers so JSON behavior does not leak into server-rendered pages.

Use API controllers when the response is data, not an HTML view.

## Database

Add a database when a feature needs persistence. For learning, start with modules and arrays until the request flow is clear, then replace the data module with a database-backed repository.

## What Not To Edit

Do not put app features inside `CorianderCore`. Framework updates manage that directory. Keep project code in:

- `src/Routes`
- `src/Controllers`
- `src/ApiControllers`
- `src/Middleware`
- `src/Modules`
- `public/public_views`
- `nodejs/src`

If a feature feels impossible without editing `CorianderCore`, that is probably a framework improvement to report separately.

## Next

Start with [Documentation](/documentation), then use the focused reference pages when you need exact API details.
