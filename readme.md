# CorianderPHP Documentation Website

This repository contains the official CorianderPHP documentation website, framework reference pages, guided projects, live demos, and downloadable project examples.

It is a CorianderPHP application that consumes the framework. It is intentionally separate from the main CorianderPHP framework repository.

## Repository Layout

- `docs/` - Markdown documentation and guided project content.
- `src/` - Website controllers, routes, modules, middleware, API controllers, and demo logic.
- `public/public_views/` - Website views rendered by the framework.
- `public/assets/` - Built CSS, JavaScript, fonts, and images.
- `nodejs/src/` - TypeScript source for interactions, demos, and code highlighting.
- `resources/downloads/` - Source files for generated downloadable project packages.
- `public/downloads/` - Generated download folders and ZIP files.
- `tests/` - Documentation website tests and quality checks.
- `scripts/` - Maintenance scripts.
- `CorianderCore/` - Framework-managed code. Do not edit this for website features.
- `coriander` - Framework CLI entrypoint. Managed by framework updates.

## Ownership Rule

Project code must stay outside `CorianderCore`.

Use app-owned folders for documentation website behavior:

- `src`
- `docs`
- `public/public_views`
- `nodejs/src`
- `resources`
- `scripts`
- `tests`
- `database`

If a documentation feature requires editing `CorianderCore`, treat that as a framework issue or framework improvement for the main CorianderPHP repository.

## Local Setup

Install PHP dependencies:

```bash
composer install
```

Install Node dependencies:

```bash
cd nodejs
npm install
```

Build frontend assets:

```bash
npm run build-all
```

## Common Commands

Regenerate downloadable project packages:

```bash
composer generate-downloads
```

Run PHP tests and documentation quality checks:

```bash
composer test
```

Type-check TypeScript:

```bash
cd nodejs
npm run build-typescript
```

Build production assets:

```bash
cd nodejs
npm run build-prod
```

## Validation Checklist

Before merging documentation or framework-update changes, run:

```bash
composer dump-autoload
composer generate-downloads
composer test
cd nodejs
npm run build-typescript
npm run build-prod
```

The test suite includes app-owned checks for:

- Markdown rendering.
- Documentation search.
- Guided project navigation.
- Route smoke tests.
- Download ZIP presence.
- Supported code fence languages.
- Internal documentation links.
- App code accidentally added inside `CorianderCore`.
- Old `/examples` links outside redirect routes.

## Updating CorianderPHP

Do not manually edit `CorianderCore` or `coriander`.

Use the framework updater:

```bash
php coriander update --dry-run
php coriander update --yes --clear-cache
composer dump-autoload
composer generate-downloads
composer test
cd nodejs
npm run build-typescript
npm run build-prod
```

Framework updates should ideally be handled by automated pull requests in this repository. The update PR should only be merged when the documentation app tests, generated downloads, and frontend build pass.

## Guided Projects

Guided projects are configured through:

```text
src/Modules/Docs/GuidedProjectRegistry.php
```

To add a guided project:

1. Add Markdown pages under `docs/projects/{project-key}`.
2. Register the project in `GuidedProjectRegistry`.
3. Add project-specific views or partials only when needed.
4. Add downloadable source files under `resources/downloads` or configure generated package inputs in `scripts/generate-downloads.php`.
5. Run the validation checklist.

The shared guided project layout is:

```text
public/public_views/examples/project.php
```

## Downloads

Download packages are generated artifacts. Do not manually maintain `public/downloads` folders when a source file can be copied or generated.

Use:

```bash
composer generate-downloads
```

The generator currently builds:

- `public/downloads/forum-completed.zip`
- `public/downloads/shelter-api-completed.zip`

## Framework Repository

The framework itself lives separately in:

```text
https://github.com/CorianderPHP/CorianderPHP
```

Bug reports and framework behavior changes should be opened against the framework repository, not fixed by editing `CorianderCore` directly in this documentation app.
