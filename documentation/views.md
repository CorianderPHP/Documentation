# View Overview

In CorianderPHP 0.4.1, `src/Views` is the private tree for both URLs and rendering. A method suffix creates an endpoint; a plain `.php` template does not.

## Which View Do I Need?

```choices
Fixed page|Put PHP/HTML directly in a .get.php page. The framework supplies layouts and metadata.|/documentation/static-views
Data-driven page|Read the request, prepare data, and either render HTML directly or return an explicit view response.|/documentation/dynamic-views
JSON or write endpoint|Return a request-to-response callable from a method file.|/documentation/handlers
Browser interactions|Keep TypeScript beside a GET page; import reusable helpers from src/Scripts.|/documentation/page-scripts
Images and public files|Use PublicUrl and ImageHandler for deployment-safe asset URLs.|/documentation/assets
```

## Understand The Files

```structure
src/Views/
  _middleware.php         # Request gates for the entire tree
  _metadata.php           # Shared page information
  _header.php             # Shared opening HTML
  _footer.php             # Shared closing HTML
  _script.ts              # Optional shared browser initialization
  index.get.php           # GET /
  about/
    index.get.php         # GET /about
    index.ts              # Browser script for /about
  articles/
    [id].get.php          # GET /articles/42
    [id].post.php         # POST /articles/42
    show.php              # Explicitly rendered template, not a URL
```

Only `.get.php`, `.post.php`, `.put.php`, `.patch.php`, `.delete.php`, `.head.php`, and `.options.php` become endpoints. Files/directories beginning with `_` or `.` are private helpers, except recognized declarations such as `_middleware.php`.

## Direct GET Or Explicit Response?

A direct GET page outputs HTML and has **no explicit return statement**:

```html
<h1>About our team</h1>
<p>We build practical tools.</p>
```

It receives `$request` and the escaped metadata array `$metadata`. The framework executes the page once, then combines the nearest header, page HTML, scripts, and nearest footer.

A method file can instead return a callable. Its response is not automatically wrapped:

```php
<?php
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    Responses::json(['message' => 'Hello']);
```

Non-GET files must return this kind of callable. Do not mix printed HTML with a returned handler.

For prepared data and a separate template, return `Responses::view('articles/show', $data)`. This **explicit view rendering** includes layouts and ancestor directory scripts unless `layout: false`. It cannot render method files or reserved layout/declaration helpers.

## Layouts And Metadata

`_header.php` and `_footer.php` inherit independently from the nearest ancestor; they do not stack. Missing parts are simply omitted.

Metadata comes from `_metadata.php` arrays, merged from root to leaf. A child replaces matching keys; nested arrays are replaced as whole values, not recursively merged. Metadata must return an array and print nothing.

```php
<?php
return ['title' => 'My application', 'description' => 'Our public website'];
```

Layouts use `$metadata['title']`. Explicit rendering can override it with `['metadata' => ['title' => 'Article']]`; the `metadata` key must be an array. Overrides are shallow-merged before one escaping pass.

`page` is ordinary application data, not framework metadata. It may hold a document object, array, scalar, or null:

```php
Responses::view('articles/show', [
    'page' => $document,
    'metadata' => ['title' => 'Article'],
]);
```

The template and layouts receive `$page` and `$metadata` separately. Strings inside view-data arrays are escaped recursively, but object properties remain raw and need output encoding. Direct pages may define their own `$page`; page-local variables still do not reach layouts.

If you used `$page` for metadata in 0.4.0, rename those reads and overrides; there is no compatibility alias. See [Upgrade Guide](/documentation/upgrades).

Metadata and explicit string-array data are HTML-escaped. Raw request values and locally prepared direct-page data are not. Encode them for the output context.

Start with the [Static View Guide](/documentation/static-views), then use [Dynamic View Guide](/documentation/dynamic-views) for request data and services.
