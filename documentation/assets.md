# Assets And Images

Use this guide when a view needs public files such as images, CSS-generated assets, downloads, or framework-aware image rendering.

## Public Asset Folder

Store public files under `public/assets`:

```structure
public/
  assets/
    img/
    css/
    js/
```

Use the framework URL helper so the same template works with either document root:

```html
<img src="<?= \CorianderCore\Core\Support\PublicUrl::asset('assets/img/logo.png') ?>" alt="Site logo">
```

If your web server document root is already `public/`, configure `PUBLIC_URL_PREFIX=` in `.env`. If your server exposes the project root, configure `PUBLIC_URL_PREFIX=/public`.

## Version CSS And JavaScript

Use `PublicUrl::versionedAsset()` for browser-cache invalidation:

```html
<link rel="stylesheet" href="<?= \CorianderCore\Core\Support\PublicUrl::versionedAsset('assets/css/output.css') ?>">
```

In 0.4.1, CSS and JavaScript URLs include a content hash. Changing the bytes changes the version even if deployment preserves modification times. Generated files carry a fingerprint that PHP reads from a bounded prefix; unmarked/manual/watch output uses a content-hash fallback. Other asset types use modification timestamps. Missing files receive an unversioned URL, so the helper does not replace build/deployment checks.

Pass a **raw relative path**. The helper encodes each URL segment; pre-encoding spaces or brackets would encode them twice. For a custom asset root, its optional second argument is the physical file to version, while the first argument remains the public URL path.

```php
use CorianderCore\Core\Support\PublicUrl;

$url = PublicUrl::versionedAsset('assets/js/pages/users/[id].js');
$customUrl = PublicUrl::versionedAsset('assets/css/theme.css', '/srv/shared/theme.css');
```

The physical path must be an app-controlled file, not visitor input. Automatic page scripts use this same helper. Do not hard-code or interpret the version as a timestamp. The standard build adds CSS fingerprints automatically; see [NodeJS Integration](/documentation/nodejs) and [Page Scripts](/documentation/page-scripts).

## Rendering Images With ImageHandler

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

## Supported Image Options

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

## Public URL Prefix

If your web server serves the project root instead of the `public/` directory, set:

```env
PUBLIC_URL_PREFIX=/public
```

When the document root is already `public/`, leave the prefix empty:

```env
PUBLIC_URL_PREFIX=
```

URLs generated from `/public/assets/...` are emitted as `/assets/...` when the prefix is empty.

## Older ImageHandler Calls

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

