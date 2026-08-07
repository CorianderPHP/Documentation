# Install CorianderPHP

Start from a framework release when you want a normal CorianderPHP project. Do not clone the documentation website as your application.

## Download A Release

Open the CorianderPHP framework releases page and download the version you want to build with:

- [CorianderPHP releases](https://github.com/CorianderPHP/CorianderPHP/releases)

Use the latest stable release for a normal project. Use a prerelease only when you intentionally want to test the newest framework changes.

## Prepare The Project Folder

Extract the release archive into your project folder.

Keep the framework and starter application files, but do not copy repository-maintenance files into your app unless you explicitly need them.

You normally do not need:

```structure
.github/
docs/
AGENTS.md
LICENSE
readme.md
```

Keep application and framework files such as:

```structure
CorianderCore/
config/
database/
nodejs/
public/
src/
.env-example
composer.json
composer.lock
coriander
index.php
```

If a release contains extra project-maintenance files, treat them the same way: useful for the framework repository, but not required for a new app.

## Install PHP Dependencies

Install Composer dependencies from the project root:

```bash
composer install
```

This installs the PHP packages needed by the framework and creates the Composer autoloader.

## Install Frontend Dependencies

Install NodeJS dependencies through the framework command:

```bash
php coriander nodejs run install
```

This runs the NodeJS install command from the `nodejs` folder without making you manually move into that directory.

## Configure Environment

Copy the example environment file:

```bash
cp .env-example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env-example .env
```

Update `.env` for your local URL, database, and public URL prefix.

If your server exposes the project root, keep:

```env
PUBLIC_URL_PREFIX=/public
```

If your server document root is already the `public/` folder, use:

```env
PUBLIC_URL_PREFIX=
```

## Build Frontend Assets

For production assets, run:

```bash
php coriander nodejs run build-prod
```

During development, use the NodeJS command documented in [NodeJS Integration](/documentation/nodejs).

## Next Steps

After installation, create your first route, controller, and view:

- [Routing](/documentation/routing)
- [Controllers](/documentation/controllers)
- [Views](/documentation/views)
- [Recommended App Architecture](/documentation/app-architecture)
