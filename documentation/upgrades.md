# Upgrade Guide

The framework updater replaces managed core files, not application code. Documentation, routes, templates, middleware policy, Composer mappings, and `public/index.php` need a separate compatibility review.

## Safe Update Routine

```workflow
Record the version|Run php coriander version and read that release's migration notes.
Protect your work|Commit app changes and back up the database and environment configuration.
Preview|Run php coriander update --dry-run and inspect skipped or changed files.
Update on a branch|Run the updater, then migrate affected app-owned code.
Validate|Build assets, run app tests, and exercise pages, authentication, writes, and errors.
Deploy|Release only after the app and downloadable examples work on the same version.
```

Keep `CorianderCore` and `coriander` untouched. Report core defects upstream instead of maintaining patches that the next update replaces.

## Upgrade From 0.2.x To 0.3.0

This is a breaking routing/view release. See the [0.3.0 release notes](https://github.com/CorianderPHP/CorianderPHP/releases/tag/v0.3.0). Do not deploy a core-only update into an app still using the old bootstrap.

### Replace Route Registration

Move each registered method into a method file:

| Old definition | New file |
| --- | --- |
| GET / | `src/Routes/index.get.php` |
| GET /articles/{id} | `src/Routes/articles/[id].get.php` |
| POST /articles | `src/Routes/articles/index.post.php` |

Each file returns a callable which returns a `ResponseInterface`. Remove `public/routes.php` includes, `$router->get/post/group()`, controller discovery, action attributes, and view fallback. Put regex/id constraints in handlers or middleware.

```php
<?php
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    Responses::view('home', ['title' => 'Home']);
```

### Replace The Bootstrap

Use the 0.3.0 starter as a reference for your app-owned `public/index.php`. Preserve environment/timezone/cookie configuration. Build a request with `RequestFactory::fromGlobals()`, call `Router::handle()`, catch failures with `ErrorResponse::fromException()`, and emit with the original method.

The old Container and `dispatch()` are removed. See [Request Lifecycle](/documentation/request-lifecycle) for the exact request-handling excerpt.

### Move Views And Metadata

- `public/public_views/home/index.php` becomes `src/Views/home.php`.
- `public/public_views/articles/show/index.php` becomes `src/Views/articles/show.php`.
- Shared header/footer become `src/Views/_header.php` and `_footer.php`.
- Pass title/description as view data instead of including `metadata.php`.
- Return `Responses::view()` or `ViewRenderer::response()`; do not call removed `render()`.

Review layouts for nearest-ancestor inheritance. Header/footer resolve independently, do not stack, and may be omitted. Use `layout: false` for fragments.

String values passed in arrays are escaped by the renderer; remove duplicate HTML escaping of those values. Objects and executable contexts still need explicit handling.

Sitemap metadata scanning is removed. Add public URLs through `SitemapHandler::addDynamicPage()` and return `toXml()`.

### Move Middleware To Directories

Root `src/Routes/_middleware.php` returns an array of PSR-15 middleware instances. Add admin gates in `src/Routes/admin/_middleware.php` and retain root security protections.

There is no implicit CSRF exemption for `/api`. Cookie-authenticated APIs send `csrf_token` in parsed JSON/form bodies. Explicitly exempt only stateless prefixes that do not use browser login cookies.

### Start Sessions Where Needed

The bootstrap configures cookies but does not eagerly open a session. Call `SessionBootstrap::start()` before auth or flash reads/writes. CSRF helpers start sessions when needed. Test that logged-in users remain recognized.

### Update Application Autoloading

The starter uses `"App\\": "src/"`. Update imports/namespaces when adopting it, then run `composer dump-autoload`.

Existing coordination classes can remain ordinary application classes; they are not automatically exposed as endpoints. `src/Actions` is a useful convention, not a required framework API.

### Remove Old Cache Commands

Route maps refresh automatically in production, normally on a 30-second interval. Remove controller-cache builds from deploy scripts. `php coriander routes:list` inspects current files independently of cache.

### Review Hosting And Dependencies

Prefer a web document root pointing at `public/`. Keep deny rules for private files if root hosting is unavoidable. Only the front controller should be directly executable as public PHP.

Set `APP_ENV=production`/`APP_DEBUG=0` explicitly. Existing environments are not automatically rewritten. Reinstall frontend dependencies from the release's updated lockfile; 0.3.0 includes the patched `source-map-js` dependency.

## Migration Checks

```bash
composer dump-autoload
php coriander routes:list
php coriander nodejs run build-prod
composer test
```

Test GET/HEAD, missing paths, 405/Allow, layouts, form tokens, login/logout, admin permissions, parsed JSON, body limits, and actual repository writes. If distributing guided project downloads, regenerate and run them too.

The [0.2.3.3 security release notes](https://github.com/CorianderPHP/CorianderPHP/releases/tag/v0.2.3.3) describe the previous router model; use this page's 0.3.0 steps for current applications.
