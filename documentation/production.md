# Production Checklist

Use this checklist before deploying a CorianderPHP app.

## Environment

Production should not use local debug settings:

```env
APP_ENV=production
APP_DEBUG=0
APP_TIMEZONE=Europe/Paris
LOG_LEVEL=warning
LOG_FORMAT=json
```

Keep secrets in `.env` or host-managed secret storage. Do not commit real credentials.

## Public Root

Point the web server document root to the project public entry point expected by your setup.

On shared hosting or Plesk, confirm:

- requests reach `index.php`
- `.htaccess` rewrite rules are active
- static assets under `public/assets` return the correct MIME type
- the public URL prefix matches how the host serves the project

If CSS is returned as `text/html`, the asset path is being routed to the app instead of the real CSS file.

## HTTPS And Proxies

Use HTTPS in production.

When the app runs behind a reverse proxy, configure trusted proxies:

```env
TRUSTED_PROXIES=127.0.0.1,::1,10.0.0.0/8
```

Only trusted proxy IPs may influence HTTPS detection from forwarded headers.

## Database

Choose the database intentionally:

- SQLite for small or local deployments.
- MySQL for most hosted multi-user apps.

Run migrations during deployment:

```bash
php coriander migrate
```

Do not edit already-run migration files in production.

## Writable Paths

Make only required runtime folders writable by PHP.

Common writable areas:

- logs
- cache
- SQLite database directory, when using SQLite
- generated files, if the app creates them

Do not make the whole project world-writable.

## Frontend Assets

Build assets before deployment:

```bash
php coriander nodejs run build-prod
```

Commit or deploy the generated assets if the host does not build Node assets during release.

## Security

Before release:

- keep `APP_DEBUG=0`
- use HTTPS
- configure `TRUSTED_PROXIES`
- keep CSRF tokens in mutating web forms
- validate request data server-side
- escape public strings in views
- protect admin routes with middleware
- check that API payload limits fit the project

## Logs

Use JSON logs when possible:

```env
LOG_CHANNEL=file
LOG_FORMAT=json
LOG_LEVEL=warning
```

Confirm the log path is writable and rotated.

## Framework Updates

Do not edit `CorianderCore` for app behavior. When the framework updates, review:

1. route smoke tests
2. documentation quality tests
3. generated downloads
4. frontend build
5. deployment-specific `.env` changes

## Final Verification

Run:

```bash
composer dump-autoload
composer generate-downloads
composer test
php coriander nodejs run build-prod
```

Then check the deployed site with real URLs, not only local paths.
