# Install CorianderPHP

Start from a framework release when you want a normal CorianderPHP project. Do not use the documentation website repository as your application, and do not copy documentation-only files into a new app.

## Download A Release

Open the CorianderPHP framework releases page and download the version you want to build with:

- [CorianderPHP releases](https://github.com/CorianderPHP/CorianderPHP/releases)

Use the latest stable release for a normal project. Use a prerelease only when you intentionally want to test the newest framework changes.

## Prepare The Project Folder

Extract the release archive into your project folder. A release archive is the easiest path because it is meant to become an application.

If you use the GitHub source tree instead of a release archive, remove repository-only files before treating the folder as your app. These files are useful for maintaining the framework repository, but they are not needed to run a normal application.

You normally do not need:

```structure
.github/
docs/
AGENTS.md
readme.md
```

Remove only files you recognize as repository maintenance material. Keep required license notices when redistributing framework code; runtime requirements and license obligations are different things.

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

The 0.3.0 starter defaults to production with debug disabled. On a trusted local machine, explicitly use:

```env
APP_ENV=local
APP_DEBUG=1
PROJECT_URL=http://localhost:8080
```

Use `APP_ENV=production` and `APP_DEBUG=0` on a public server. Existing environment files are not rewritten by framework updates.

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

To try the app locally with the public directory as document root:

```bash
php -S localhost:8080 -t public public/index.php
```

Use `PUBLIC_URL_PREFIX=` for this command. The PHP development server is not a production server.

## Next Steps

The 0.3.0 starter discovers method files under `src/Routes`. `index.get.php` defines the homepage. A template alone does not create a URL. Follow [Static View Guide](/documentation/static-views) for a fixed page or [Dynamic View Guide](/documentation/dynamic-views) for prepared data.

After installation, create your first route and view:

- [Routing](/documentation/routing)
- [Request Handlers](/documentation/handlers)
- [Views](/documentation/views)
- [Recommended App Architecture](/documentation/app-architecture)
