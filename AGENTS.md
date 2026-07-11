# AGENTS.md

## Project Role

This repository contains the official CorianderPHP documentation website, guided projects, demo applications, and generated downloads.

It is not the CorianderPHP framework source repository.

## Hard Rule

Do not edit `CorianderCore/` manually.

`CorianderCore/` and the `coriander` CLI file are framework-managed files. They are updated from `CorianderPHP/CorianderPHP` releases.

If framework behavior needs to change, open an issue or pull request in:

https://github.com/CorianderPHP/CorianderPHP

## App-Owned Code

Documentation website code should live in:

- `src/`
- `docs/`
- `public/public_views/`
- `public/assets/`
- `resources/`
- `scripts/`
- `tests/`

## Generated Files

Do not maintain project download archives by hand.

Use:

```bash
php scripts/generate-downloads.php
```

## Framework Updates

Framework updates should be handled by `.github/workflows/update-framework.yml` or by the framework updater command.

The framework README and this documentation website are intentionally separate. Do not blindly sync README content into the website.

Framework update pull requests should review documentation impact and update website docs only when the release changes behavior that users need to understand.

## Validation

After documentation, demo, or asset changes, run the relevant checks:

```bash
composer test
cd nodejs && npm run build-prod
php scripts/generate-downloads.php
```

## Git

Use gitmoji commit messages and end commit messages with a period.
