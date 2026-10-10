# CLI Guide

Run commands from the project root:

```bash
php coriander
php coriander help
```

The first shows an overview. Detailed help includes command descriptions and examples:

```bash
php coriander help make
php coriander help nodejs
php coriander help migrate
php coriander help routes
php coriander --help
php coriander -h
php coriander make --help
php coriander nodejs --help
```

## Create Routes And Views

```bash
php coriander make:route about
php coriander make:route "articles/[id].post"
php coriander make:view articles/show
```

The route generator defaults to GET and creates a method file under `src/Routes`. Files are discovered automatically; no registration follows. The view generator creates a PHP template under `src/Views`.

Generators are optional. They reject invalid/private names and route conflicts before writing files. Invalid syntax returns 2; conflicts or invalid existing definitions return 1.

The controller generator and manual route registration were removed in 0.3.0. Write small route handlers or ordinary app-owned action classes instead.

## Inspect Routes

```bash
php coriander routes:list
```

Shows discovered URLs, methods, relative files, HEAD fallbacks, and inherited middleware. It uses current files even when production has a cached route map. It does not execute handlers/middleware or change the cache.

## Database And Migrations

```bash
php coriander make:database
php coriander make:migration CreateUsersTable
php coriander migrate:status
php coriander migrate --dry-run
php coriander migrate
php coriander migrate:rollback --dry-run
php coriander migrate:rollback --step=2
```

`make:database` prompts for connection configuration. Migration files live in `database/migrations`; applied migrations are tracked by the framework.

Do not edit an applied migration in production. The local/development-only `--allow-changed` migration option is not a production repair strategy. See [Database](/documentation/database).

## Frontend Tooling

```bash
php coriander nodejs run install
php coriander nodejs ci
php coriander nodejs run build-ts
php coriander nodejs run build-prod
```

The wrapper runs Node commands in `nodejs` and propagates failures to scripts/CI. Available scripts depend on your `nodejs/package.json`; the released starter provides the build scripts above.

## Cache And Sitemap

```bash
php coriander cache clear
php coriander make:sitemap
```

Route maps refresh automatically in production. There is no `cache controllers` step. See [Route Cache](/documentation/cache) and [Sitemap](/documentation/sitemap).

## Framework Updates

```bash
php coriander version
php coriander update --dry-run
php coriander update --yes
```

The updater manages `CorianderCore` and `coriander`, not your routes, templates, Composer mappings, or bootstrap. Read the [Upgrade Guide](/documentation/upgrades) when a release changes these app-owned contracts.

Useful update options:

- `--dry-run`: inspect the plan without writing.
- `--yes`: skip interactive confirmation.
- `--clear-cache`: clear caches after updating.
- `--pre-release`: allow a prerelease; stable releases remain preferred.
- `--backup-dir=backups/custom`: safe relative backup location.
- `--auth-token=...`: satisfy an updater policy token when configured.
- `--force`: overwrite Git-detected local changes; use only after reviewing them.

The updater creates backups, rolls back failed writes, runs `composer dump-autoload`, and reports skipped/updated files. Git protection needs an available Git checkout; do not treat it as a replacement for version control and backups.

Production updates are denied by default unless `CORIANDER_UPDATER_ALLOW_PRODUCTION=1` permits them. Other policy controls include `CORIANDER_UPDATER_ENABLED`, `CORIANDER_UPDATER_AUTH_TOKEN`, and `CORIANDER_UPDATER_MAX_ATTEMPTS_PER_HOUR`.

## Exit Codes

Success returns 0. Execution failures normally return 1, invalid usage 2, and unknown commands/subcommands 3. External-process wrappers propagate the external exit code when available. Check both output and exit status in automation.
