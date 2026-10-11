# Dynamic View Guide

Use a dynamic page when the request identifies content or a service must prepare data. The framework supports direct GET pages and explicit rendering of a separate template.

## First Build A Direct Page

Create `src/Views/articles/[id].get.php`. You can generate it:

```bash
php coriander make:view "articles/[id].get"
```

A request for `/articles/42` exposes `42` as `$request->getAttribute('id')`. The segment is raw input, not a validated integer.

Replace the generated page:

```html
<?php
$id = (string) $request->getAttribute('id');
$articles = [
    42 => ['title' => 'Render prepared data', 'body' => 'Keep SQL outside templates.'],
];
$article = ctype_digit($id) && (int) $id > 0 ? ($articles[(int) $id] ?? null) : null;
?>
<?php if ($article !== null): ?>
    <article>
        <h1><?= htmlspecialchars($article['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($article['body'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    </article>
<?php else: ?>
    <p>Article not found.</p>
<?php endif; ?>
```

This deliberately uses a small local array so you can verify the URL before adding persistence. These strings were assigned **inside the page**, so encode them before HTML output. The renderer does not sanitize local variables or raw request values.

The simple missing-record branch above still returns HTTP 200. Use the handler approach below when you need a real 404, a redirect, or another response status.

## Separate Request Flow From Rendering

For a larger page, replace that same method file with a callable:

```php
<?php
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    $id = (string) $request->getAttribute('id');
    if (!ctype_digit($id) || (int) $id < 1) {
        return Responses::html('Article not found.', 404);
    }

    $articles = [
        42 => ['title' => 'Render prepared data', 'body' => 'Keep SQL outside templates.'],
    ];
    $article = $articles[(int) $id] ?? null;
    if ($article === null) {
        return Responses::html('Article not found.', 404);
    }

    return Responses::view('articles/show', [
        'page' => ['title' => $article['title'], 'description' => 'Read this article.'],
        'article' => $article,
        'canEdit' => false,
    ]);
};
```

Do not keep the direct HTML after this handler. A method file cannot print HTML and return a callable.

Create the separate **unsuffixed** `src/Views/articles/show.php` by hand:

```html
<article>
    <h1><?= $article['title'] ?></h1>
    <p><?= $article['body'] ?></p>
    <?php if ($canEdit): ?>
        <a href="/articles/42/edit">Edit article</a>
    <?php endif; ?>
</article>
```

Unlike direct-page local data, strings passed to `Responses::view()` are recursively HTML-escaped. Output these prepared strings directly. The `page` key is reserved for a metadata array; use `article` for the content record.

The template does not expose `/articles/show`. The method file owns the URL and chooses a template. `Responses::view('articles/[id].get')` is invalid: method files must be dispatched by the router.

## Add Your Repository

Replace the local array lookup with an app-owned repository call. See [Database Patterns](/documentation/database-patterns) for real SQLManager examples. A missing row should return 404 before rendering.

Move repeated coordination into an ordinary `App\Actions` class when it makes the handler easier to follow. Compute permissions in an app service, then pass display flags to the view. Hiding a button is not server-side authorization.

```workflow
Method file|GET /articles/42 selects the callable in articles/[id].get.php.
Handler|Validates the id, asks a repository for data, and chooses the response.
Explicit view|Responses::view('articles/show', ...) prepares escaped variables.
Template|show.php renders the record; inherited layouts receive the same data.
Response|The emitter sends HTML, or the handler's 404/redirect/JSON response.
```

## Layout Data And Request Attributes

Direct pages/layouts receive the middleware-adjusted `$request` and escaped `$page` metadata. A variable set in a direct page is not available in its header/footer.

Explicit templates and layouts receive the variables passed to `Responses::view()`. Pass request-derived display values deliberately; do not assume the explicit renderer supplies a `$request` variable.

Metadata shallow-merges root to leaf. Explicit `['page' => ['title' => 'Override']]` overrides matching metadata keys. Keep layout declarations free of database/business side effects.

## Forms And Redirects

A write needs a separate POST method file and a CSRF token in the form:

```html
<form method="POST" action="/articles">
    <?= \CorianderCore\Core\Security\Csrf::input() ?>
    <input name="title" value="<?= $old['title'] ?? '' ?>">
    <button type="submit">Save article</button>
</form>
```

Prepare `old` and validation messages in the handler and render them through `Responses::view()`. Root middleware checks CSRF before the write. After persistence, return `Responses::redirect('/articles', 303)` so the next page is a GET.

See the [forum handler chapter](/guided-projects/forum/handlers) for sessions, flashes, and permission-aware request flow.

## Scripts And Fragments

Explicit full views load ancestor `_script.js` modules based on the **template's directory**, not the request URL. An unsuffixed template has no automatic paired page module; import its behavior from a directory `_script.ts`.

```php
return \CorianderCore\Core\Http\Responses::view(
    'articles/summary',
    ['article' => $article],
    layout: false,
);
```

Fragments skip both layouts and automatic script tags. JSON, redirects, and explicit HTML responses are never decorated. Read [Page Scripts](/documentation/page-scripts) before adding manual tags.

## Checkpoint

Visit `/articles/42`, then `/articles/999` and an invalid id. The callable version must return 200 for the known record and 404 otherwise. Check the title, HTML escaping, and inherited layout.

Use fixed or allowlisted template names, never raw visitor input. View objects are not recursively sanitized; encode their properties and non-HTML contexts explicitly.
