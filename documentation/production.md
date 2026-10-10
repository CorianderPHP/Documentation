# Production Checklist

Deploy tested app-owned code together with the framework version it supports. A successful core update alone is not a successful application migration.

## Environment And Dependencies

```env
APP_ENV=production
APP_DEBUG=0
PROJECT_URL=https://example.com
PUBLIC_URL_PREFIX=
```

Use your real HTTPS URL. Keep secrets out of Git and PHP `display_errors=0`. Install production Composer dependencies and build assets before serving traffic.

```bash
composer install --no-dev --optimize-autoloader
php coriander nodejs ci
php coriander nodejs run build-prod
```

Run tests in CI before installing without development packages.

## Document Root And Private Files

Prefer `public/` as the document root. This keeps `src/Views`, routes, environment files, database, and framework source outside direct web access.

If your host serves the project root, use `PUBLIC_URL_PREFIX=/public` and explicit Apache/nginx deny rules before static-file/application rewrites. Protect hidden files, source/config/vendor, databases, cache, logs, resources, scripts, tests, and backups.

Do not rely only on "rewrite missing files": existing private files could still be served. Restrict directly executable PHP to the front controller. Test both source exposure and real asset URLs.

## Sessions, HTTPS, And Middleware

Configure HTTPS and only trusted reverse proxies. Keep root security headers, request limits, and CSRF middleware. Verify admin child middleware and cookie-authenticated API tokens.

Sessions start when authentication/flash/CSRF needs them; public pages can remain session-free. Do not remove session startup from auth services.

A stateless writable API needs authentication, authorization, and abuse controls; a CSRF exception alone provides none of these.

## Database And Permissions

Back up the database, apply migrations deliberately, and make only necessary storage/cache directories writable. SQLite needs a writable parent directory; a MySQL deployment needs reviewed dialect/constraint behavior.

Do not expose a tutorial's fixed demo credentials as production authentication. The hosted demo's fake-success writes are for safety, not a production persistence strategy.

## Cache And Release Checks

Production route maps refresh automatically, normally after 30 seconds. Clear caches after deploy when immediate route changes are required. Confirm the route list, logs, and health checks.

Verify homepage, representative docs/pages, authentication, admin denial, writes, JSON errors, 404, 405/Allow, and body-free HEAD requests. See [Upgrade Guide](/documentation/upgrades) when crossing 0.2.x to 0.3.0.
