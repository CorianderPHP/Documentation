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

## Documentation Repository Automation

For this documentation website, framework update pull requests should:

1. update framework-managed files
2. include the framework release notes in the PR description
3. run generated downloads
4. run tests
5. run frontend builds

That keeps documentation and demos aligned with the framework without manually checking every small release.

## When An Update Breaks App Code

Do not patch `CorianderCore` locally as a permanent fix.

Instead:

1. confirm the break with a focused test
2. update app-owned code when the framework behavior is correct
3. open a framework issue when the framework behavior is wrong
4. add documentation notes if the change affects users

## Rollback

Use Git first. Framework updates should be reviewed in a branch or pull request.

If the framework updater created backups and rollback support is available for your version, use the documented rollback command for that release. Still prefer Git for project-level rollback because it includes app-owned files and generated artifacts.
