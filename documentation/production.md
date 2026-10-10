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

- requests reach `public/index.php`
- `.htaccess` rewrite rules are active
- static assets under `public/assets` return the correct MIME type
- the public URL prefix matches how the host serves the project

If CSS is returned as `text/html`, the asset path is being routed to the app instead of the real CSS file.

If the document root is the project root, rewrites alone do not protect existing files. Deny direct access to `.env`, hidden files, `CorianderCore`, `src`, `config`, `vendor`, `nodejs`, databases, logs, and `public/public_views`. Keep these deny rules before asset and application rewrites. Use equivalent rules in nginx when Apache `.htaccess` is not applied. See the [v0.2.3.3 upgrade checklist](/documentation/upgrades) for the app-owned files to review.

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

For SQLite, the deployment user must be able to create/open the adjacent `.coriander-migrations.lock` file. Do not remove it during a running deployment. With MySQL, schema statements may commit implicitly even when a migration fails; do not rely on transactional rollback as a replacement for backups.

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
- register every public route explicitly and avoid legacy automatic aliases around protected actions
- check that API payload limits fit the project

Verify normal GET and HEAD requests after deployment:

```bash
curl -i https://your-app.example/
curl -I https://your-app.example/
```

Both should return the expected status and headers; HEAD must not send an HTML body. Check that requesting `.env` or a PHP file under `src/` returns a denied response without exposing its contents.

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

```choices
Route smoke tests|Confirm public pages, documentation routes, demos, and downloads still respond.
Documentation quality tests|Confirm links, supported code fences, and guided project navigation are still valid.
Generated downloads|Regenerate and verify completed project packages.
Frontend build|Rebuild TypeScript and Tailwind assets.
Environment review|Check deployment-specific `.env` changes before release.
```

## Final Verification

Run:

```bash
composer dump-autoload
composer generate-downloads
composer test
php coriander nodejs run build-prod
```

Then check the deployed site with real URLs, not only local paths.
