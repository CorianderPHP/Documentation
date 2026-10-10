# View Overview

Templates live in `src/Views`, outside the public web directory. Routes render them explicitly with `Responses::view()`. A template does not create a URL.

## Which View Do I Need?

```choices
Static view|A fixed page such as about, contact, or legal information.|/documentation/static-views
Dynamic view|A page whose handler prepares article data, permissions, forms, or database rows.|/documentation/dynamic-views
Assets and images|Public files, URL prefixes, and ImageHandler.|/documentation/assets
```

Read [Static View Guide](/documentation/static-views) or [Dynamic View Guide](/documentation/dynamic-views). Use [Assets And Images](/documentation/assets) for media.

Both view types use the same renderer and layout rules. "Static" describes the content, not automatic routing or a different template engine.

## Shared Layouts

```structure
src/Views/
  _header.php
  _footer.php
  home.php
  admin/
    _header.php
    users/show.php
```

When rendering `admin/users/show`, the renderer finds the nearest `_header.php` and nearest `_footer.php` independently. This example uses the admin header and root footer.

Layouts do not stack. Each header/footer is included at most once. If one is absent in the entire ancestry, that part is omitted. Parent layouts still apply when there are no local layout files.

Use `Responses::view('fragment', $data, layout: false)` to skip layout lookup for a fragment.

## Data And Escaping

Header, page, and footer receive the same passed data. String values in arrays are recursively HTML-escaped; do not escape them a second time in the template. Objects are not recursively sanitized: encode untrusted object properties yourself or pass arrays of plain data.

HTML escaping does not validate URL schemes or make JavaScript/CSS contexts safe. Keep visitor input out of executable contexts and validate external URLs.

Every render evaluates templates again with the current data. Define reusable functions/classes in autoloaded app files, not in repeatedly included templates.

0.3.0 removed metadata files and `public/public_views`. Pass title/description as view data and use explicit sitemap URLs; see [Upgrade Guide](/documentation/upgrades).
