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

Reference public assets with absolute paths:

```html
<img src="/public/assets/img/logo.png" alt="Site logo">
```

If your web server document root is already `public/`, configure `PUBLIC_URL_PREFIX=` in `.env`. If your server exposes the project root, configure `PUBLIC_URL_PREFIX=/public`.

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

