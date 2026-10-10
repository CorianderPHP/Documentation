# Static View Guide

A static view is a page rendered directly from `public/public_views` without a controller preparing data first. It is useful for home pages, about pages, contact pages, legal pages, and simple content pages.

## Create The View

Generate the view with the CLI:

```bash
php coriander make:view about
```

This creates:

```structure
public/
  public_views/
    about/
      index.php
      metadata.php
```

## Register The URL

Add this to `public/routes.php`:

```php
use CorianderCore\Core\Router\ViewRenderer;

$router->get('/about', static fn () => (new ViewRenderer())->render('about'));
```

Now `/about` renders `public/public_views/about/index.php`. Creating the folder alone does not expose a page: routing is explicit by default. Register `/` separately when the view is your homepage.

## Request Flow

```workflow
Request|The browser opens `/about`.
Route|The registered GET route calls `ViewRenderer::render('about')`.
View folder|The renderer resolves `public/public_views/about`.
Metadata|`metadata.php` provides the page title, description, and sitemap settings.
Template|`index.php` renders the HTML content.
```

## Write The Template

Edit `public/public_views/about/index.php`:

```html
<main class="mx-auto max-w-3xl px-4 py-10">
    <h1>About our app</h1>

    <p>
        We build small tools for local communities.
    </p>

    <a href="/contact">Contact us</a>
</main>
```

This file should contain presentation markup only. A static view is a good place for text, links, images, and sections that do not require PHP data.

## Configure Metadata

Edit `public/public_views/about/metadata.php`:

```php
<?php
$metadata = '
    <title>About - My App</title>
    <meta name="description" content="Learn more about My App." />
';

$addViewInSitemap = true;
$sitemapPriority = 0.7;
```

Use `metadata.php` for page metadata and sitemap settings. Do not load database data from this file.

The renderer evaluates shared templates and view files on each render. In your shared `header.php`, load the selected metadata with `require`, not `require_once`, so rendering a second page does not reuse or skip the previous page's metadata.

## Add Static Assets

Store public assets under `public/assets`, then reference them with absolute public paths:

```html
<img src="/public/assets/img/team.jpg" alt="The team">
```

If your app may run with a different `PUBLIC_URL_PREFIX`, use [Assets And Images](/documentation/assets) so the framework can resolve the public asset URL for you.

## When Static Is Enough

Keep the page static when:

- the content is mostly fixed
- the page does not need route parameters
- the page does not submit a form
- the page does not need user-specific permissions
- the page does not read database rows

Move to a [dynamic view](/documentation/dynamic-views) when the page needs prepared data from a controller.

