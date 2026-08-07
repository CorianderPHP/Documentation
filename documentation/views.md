# View Guide

CorianderPHP views live in `public/public_views`. Each view contains an `index.php` template and a `metadata.php` file for sitemap settings.

## Creating a View

Generate a view with the CLI:

```bash
php coriander make:view Home
```

The command creates `public/public_views/home/index.php` and `metadata.php`. Update `metadata.php` to control sitemap inclusion:

```php
<?php
$addViewInSitemap = true;      // include page
$sitemapPriority  = 0.8;       // 0.0 - 1.0
```

## Rendering Images

Use the built-in `ImageHandler` when a view needs to render an image through the framework asset path rules:

```php
<?= \CorianderCore\Core\Image\ImageHandler::render('/public/assets/img/logo.png', [
    'alt' => 'Site logo',
    'class' => 'h-10 w-auto',
    'quality' => 80,
    'loading' => 'lazy',
    'decoding' => 'async',
]); ?>
```

The helper outputs a `<picture>` tag and handles `PUBLIC_URL_PREFIX` automatically.

For PNG and JPEG images, it can generate a WebP source. For SVG and WebP images, it renders the original file directly without unnecessary conversion.

Supported render options include:

- `alt`: image alternative text.
- `class`: CSS classes for the generated `<img>`.
- `imgClass`: explicit CSS classes for the generated `<img>`.
- `pictureClass`: CSS classes for the generated `<picture>`.
- `quality`: WebP conversion quality from `0` to `100`.
- `convert`: set to `false` to skip WebP conversion.
- `width` and `height`: explicit image dimensions.
- safe image attributes such as `loading`, `decoding`, `fetchpriority`, and `data-*`.

Unsafe attributes are ignored, including event-handler attributes such as `onclick`, and generated source attributes such as `src` and `srcset`.

If your web server serves the project root instead of the `public/` directory, set the public URL prefix in `.env`:

```env
PUBLIC_URL_PREFIX=/public
```

When the document root is already `public/`, leave the prefix empty. URLs generated from `/public/assets/...` are emitted as `/assets/...`.

Since CorianderPHP v0.2.3.1, `ImageHandler::render()` uses this options-array format. Older positional calls should be updated:

```php
// Old
<?= \CorianderCore\Core\Image\ImageHandler::render('/public/assets/img/logo.png', 'Site logo', '', 'h-10 w-auto', 80); ?>

// New
<?= \CorianderCore\Core\Image\ImageHandler::render('/public/assets/img/logo.png', [
    'alt' => 'Site logo',
    'class' => 'h-10 w-auto',
    'quality' => 80,
]); ?>
```

## Output Safety

Variables passed to view templates are automatically escaped for HTML output to mitigate XSS attacks.

## Path Safety

View resolution only accepts normalized relative paths under `public/public_views`.

- Dot-segments such as `.` or `..` are rejected.
- Absolute paths are rejected.
- Null-byte path fragments are rejected.

This prevents path traversal and accidental inclusion of files outside the view root.

Shared templates now use an internally normalized requested-view value for metadata and asset resolution, instead of raw request path data.

## Best Practices

- Keep view logic minimal; handle business logic in controllers or services.
- Avoid double escaping; variables provided to views are sanitized by the framework.
- Store assets under `public/assets` and reference them with absolute paths.
