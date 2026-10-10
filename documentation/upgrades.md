# Upgrade Guide

CorianderPHP is designed so framework updates can replace framework-owned files without deleting app behavior.

## What The Framework Owns

Treat these as framework-managed:

```structure
CorianderCore/
coriander
```

Do not add project behavior there.

## What The App Owns

Keep project behavior in:

```structure
src/
public/public_views/
documentation/
database/
nodejs/
resources/
scripts/
tests/
```

These folders should contain your routes, controllers, modules, views, documentation, assets, migrations, and tests.

## Update Flow

Preview first:

```bash
php coriander update --dry-run
```

Apply when ready:

```bash
php coriander update --yes --clear-cache
```

Then run:

```bash
composer dump-autoload
composer generate-downloads
composer test
php coriander nodejs run build-prod
```

## What To Review

After an update, review:

- changed files under `CorianderCore`
- route smoke tests
- database behavior
- middleware behavior
- environment variable changes
- release notes from the framework repository

If the update changes framework behavior, update the documentation website in the documentation repository, not inside the framework core.

## Upgrade To v0.2.3.3

This security release disables automatic routing by default. Updating only `CorianderCore` and `coriander` is not enough for an app that depended on controller or view discovery. Review the [release notes](https://github.com/CorianderPHP/CorianderPHP/releases/tag/v0.2.3.3), then apply these app-owned changes.

### Register Every Public Route

In `public/routes.php`, register the homepage and include each feature's route file:

```php
use CorianderCore\Core\Router\ViewRenderer;

$router->get('/', static fn () => (new ViewRenderer())->render('home'));
$router->get('home', static fn () => (new ViewRenderer())->render('home'));

$featureRoutes = PROJECT_ROOT . '/src/Routes/feature.php';
if (is_file($featureRoutes)) {
    (require $featureRoutes)($router);
}
```

Replace `feature.php` with your actual route file. Register API methods explicitly as well and return JSON PSR-7 responses instead of arrays. Place protected actions inside the appropriate middleware group. Do not re-enable automatic routing merely to remove 404s: convention-based aliases may bypass route-specific middleware. See [Routing](/documentation/routing) and the [forum API example](/guided-projects/forum/api).

### Emit HEAD Responses Correctly

Replace the dispatch/emission call in `public/index.php`:

```php
use CorianderCore\Core\Http\RequestFactory;
use CorianderCore\Core\Http\ResponseEmitter;

$request = RequestFactory::fromGlobals();
$response = $router->dispatch($request);
ResponseEmitter::emit($response, $request->getMethod());
```

If your exception handler writes its own error body, suppress it for HEAD too:

```php
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
    echo 'Internal Server Error';
}
```

GET routes now support HEAD fallback, including their parameters and middleware. Keep GET handlers free of state-changing operations.

### Keep Rendering Repeatable

Change `require_once` to `require` for shared headers, footers, view templates, and selected metadata in app-owned rendering code. Set the requested view in a custom not-found handler before loading the header:

```php
$__corianderRequestedView = 'notfound';
require PROJECT_ROOT . '/public/public_views/header.php';
require PROJECT_ROOT . '/public/public_views/notfound/index.php';
require PROJECT_ROOT . '/public/public_views/footer.php';
```

Keep `require_once` for configuration and autoloaders. Rendering files must run again to display the current page's data and metadata.

### Protect Files At The Web Server

For Apache with the project root exposed, place these rules immediately after `RewriteEngine On`, before assets and application rewrites:

```txt
RewriteRule (^|/)\. - [F,L]
RewriteRule ^(?:CorianderCore|src|config|vendor|nodejs|database|backups|cache|logs|resources|scripts|tests)(?:/|$) - [F,L,NC]
RewriteRule ^public/(?:public_views(?:/|$)|routes\.php$) - [F,L,NC]
RewriteRule \.(?:bak(?:\.\d+)?|log|sqlite(?:3)?|db)$ - [F,L,NC]
RewriteRule ^(?:coriander|composer\.(?:json|lock|phar)|phpunit\.xml|AGENTS\.md)$ - [F,L,NC]
```

Adapt private directories to your app and preserve your host's certificate-validation configuration. On nginx, configure equivalent access restrictions; nginx does not read `.htaccess`. Verify that `.env`, private PHP files, and database files are denied while CSS, JavaScript, and intended download archives remain accessible.

If `public/.htaccess` declares its own rewrite rules, protect its private paths there too. Apache can replace parent rewrite rules in that directory. These rules are relative to `public/`, so they also work when `public/` is the document root:

```txt
RewriteEngine On
RewriteRule (^|/)\. - [F,L]
RewriteRule ^public_views(?:/|$) - [F,L,NC]
RewriteRule ^(?:routes|routes\.snippet|sitemap)\.php$ - [F,L,NC]
RewriteRule ^downloads/[^/]+/ - [F,L,NC]
RewriteRule \.(?:bak(?:\.\d+)?|log|sqlite(?:3)?|db)$ - [F,L,NC]
DirectoryIndex index.php
```

Keep your existing non-file rewrite to `index.php` after these protections. The download rule permits ZIP files but denies the unpacked source directories; omit it if your app has no such directories.

### Check Updater And Migration Permissions

The updater uses Git status to protect locally edited, renamed, deleted, and untracked managed files. Run it in a Git checkout. Prefer updating and testing in a branch, then deploying the reviewed files, rather than forcing an update on a non-Git production upload.

SQLite migrations create a persistent `.coriander-migrations.lock` file beside the database. Make that directory writable by the deployment user; do not delete the lock file while a migration may be running. SQLite migration changes and history are transactional; MySQL DDL can commit implicitly. See [Database](/documentation/database).

### Verify The Migrated App

Test the homepage, every documented API URL, admin access as a guest/member/admin, and repeated rendering with different data. Check HEAD responses and denied direct-file requests. Regenerate guided-project downloads after changing their source files so downloaded examples match the running app.

## Documentation Repository Automation

For this documentation website, framework update pull requests should:

```workflow
Framework files|Update framework-managed files from the release.
Release notes|Include the framework release notes in the PR description.
Downloads|Regenerate completed project downloads.
Tests|Run documentation and demo tests.
Frontend build|Rebuild TypeScript and Tailwind assets.
```

That keeps documentation and demos aligned with the framework without manually checking every small release.

## When An Update Breaks App Code

Do not patch `CorianderCore` locally as a permanent fix.

Instead:

```workflow
Focused test|Confirm the break with the smallest test that proves it.
App-owned fix|Update app-owned code when the framework behavior is correct.
Framework issue|Open a framework issue when the framework behavior is wrong.
Documentation note|Add documentation notes when the change affects users.
```

## Rollback

Use Git first. Framework updates should be reviewed in a branch or pull request.

If the framework updater created backups and rollback support is available for your version, use the documented rollback command for that release. Still prefer Git for project-level rollback because it includes app-owned files and generated artifacts.
