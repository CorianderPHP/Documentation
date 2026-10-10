# Static View Guide

A static view is a fixed page that does not need a data lookup. In 0.3.0 it still has a route file: copying a template alone does not expose a URL.

## Goal

Create an `/about` page with a shared header and footer.

## Create The Route And Template

You can use the generators:

```bash
php coriander make:route about
php coriander make:view about
```

Or create `src/Routes/about.get.php` and `src/Views/about.php` by hand. The generated view contains a section and heading, not a complete page layout.

Replace the route contents:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    Responses::view('about', [
        'title' => 'About us',
        'description' => 'Learn about our team.',
    ]);
```

Edit `src/Views/about.php`:

```html
<main>
    <h1>About us</h1>
    <p>We build practical tools for our community.</p>
</main>
```

The render name `about` maps to `src/Views/about.php`. For `pages/about`, the template is `src/Views/pages/about.php`.

## Add The Layout

Create `src/Views/_header.php`:

```html
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'My app' ?></title>
    <meta name="description" content="<?= $description ?? '' ?>">
</head>
<body>
```

Create `src/Views/_footer.php`:

```html
</body>
</html>
```

The framework includes header, page, then footer. The route data is available to all three. There is no separate `metadata.php`; title/description are ordinary data.

If the starter already has these layouts, update them instead of creating competing copies.

## Check The Page

```bash
php coriander routes:list
```

Confirm GET/HEAD `/about` appears, then visit it. A 404 usually means the route filename/path is wrong. A missing-view error means the render name does not match the template path.

The homepage uses `src/Routes/index.get.php`, even when its template is named `home.php`.

## Assets And Sitemap

Keep CSS, JavaScript, and images under `public/assets`. Use [PublicUrl or ImageHandler](/documentation/assets) instead of hard-coded deployment paths.

For search engines, add the public URL through [SitemapHandler](/documentation/sitemap). Views are not scanned for sitemap metadata.

Move to a [dynamic view](/documentation/dynamic-views) when request data must be prepared before rendering.
