# Static View Guide

A static page shows fixed content without a database lookup. CorianderPHP 0.4.1 can render it directly from a GET file, without a forwarding handler.

## Create Your Page

For an `/about` page, you can run:

```bash
php coriander make:view about
```

This creates `src/Views/about/index.get.php`. Replace its contents:

```html
<section>
    <h1>About us</h1>
    <p>We build tools for our community.</p>
</section>
```

You can create the same file by hand. `index.get.php` serves the directory's URL; a plain `index.php` does not.

Visit `/about`. Do not also create `about.get.php`: it would define the same GET endpoint.

## Give The Page A Title

Create `src/Views/about/_metadata.php`:

```php
<?php
return [
    'title' => 'About us',
    'description' => 'Learn about our team.',
];
```

The metadata file returns an array and prints nothing. The framework merges root metadata first, then directory metadata. Use metadata for title/description defaults, not for database calls or authentication.

## Add A Shared Layout

A fresh starter already has root layouts. Update them rather than creating competing copies. If your app has none, create `src/Views/_header.php`:

```html
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $metadata['title'] ?? 'My app' ?></title>
    <meta name="description" content="<?= $metadata['description'] ?? '' ?>">
</head>
<body>
<main>
```

Create `src/Views/_footer.php`:

```html
</main>
</body>
</html>
```

The title/description strings are already escaped by the framework. Output them directly rather than calling `htmlspecialchars()` again.

The direct page runs before the layouts, but variables assigned inside it are **not** forwarded to the header/footer. Put shared layout information in metadata. All three receive the same matched `$request`.

## Understand The Rendering Flow

```workflow
Request|GET /about selects src/Views/about/index.get.php.
Middleware|Inherited request gates run before any page code.
Metadata|Root and about metadata merge into the escaped $metadata array.
Page|The GET file executes once and produces the content.
Layout|Nearest header and footer surround the content; compiled scripts are inserted before the footer.
```

A directory can override only its header and retain the root footer, or the other way around. If no layout exists, only the page HTML appears.

## Add Browser Behavior Only When Needed

Create `src/Views/about/index.ts` beside the page, then build:

```bash
php coriander nodejs run build-prod
```

The framework automatically includes its compiled module when present. Do not add a second manual script tag. See [Page Scripts](/documentation/page-scripts) for a working interaction and shared imports. PHP-only pages do not need TypeScript or Node.js.

## Check Your Work

```bash
php coriander routes:list
```

Confirm GET and HEAD `/about` appear. Open the page and inspect its title. If the URL is 404, check file casing and the method suffix, not the template contents.

The homepage is `src/Views/index.get.php`. For images and public files, use [Assets](/documentation/assets). Add public URLs to the [Sitemap](/documentation/sitemap); views are not scanned to infer sitemap entries.

When data must come from the request or a repository, continue with [Dynamic View Guide](/documentation/dynamic-views).
