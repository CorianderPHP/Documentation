# Dynamic View Guide

A dynamic view renders data prepared by its request handler. Use it for article details, database content, permissions, form errors, or flash messages.

## Goal And Request Flow

Build `/articles/42` with a validated id and prepared article data. This first example uses a small array so you can verify routing/rendering before adding a database.

```workflow
Route file|GET /articles/42 selects src/Routes/articles/[id].get.php.
Handler|Reads and validates the id, then prepares the article data.
Response|Responses::view('articles/show', ...) renders the private template.
Template|src/Views/articles/show.php receives the article and title variables.
Layout|The nearest header/footer receive the same data.
```

## Create The Files

```bash
php coriander make:route "articles/[id].get"
php coriander make:view articles/show
```

Expected structure:

```structure
src/
  Routes/articles/[id].get.php
  Views/
    _header.php
    _footer.php
    articles/show.php
```

## Prepare Data In The Handler

Replace `src/Routes/articles/[id].get.php`:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    $id = (string) $request->getAttribute('id');
    if (!ctype_digit($id) || (int) $id < 1) {
        return Responses::html('Article not found.', 404);
    }

    $articles = [
        42 => [
            'id' => 42,
            'title' => 'Render prepared data',
            'body' => 'Keep data access outside templates.',
        ],
    ];
    $article = $articles[(int) $id] ?? null;
    if ($article === null) {
        return Responses::html('Article not found.', 404);
    }

    return Responses::view('articles/show', [
        'title' => $article['title'],
        'description' => 'Read this article.',
        'article' => $article,
        'canEdit' => false,
    ]);
};
```

The handler always returns a response, including success. A missing record is different from a malformed id, but both may use 404 on a public detail page.

When adding SQLite/MySQL, replace the array lookup with your [repository](/documentation/database-patterns). Keep the template unchanged. Do not query the database from a view or layout.

## Render The Template

Edit `src/Views/articles/show.php`:

```html
<main>
    <h1><?= $article['title'] ?></h1>
    <p><?= $article['body'] ?></p>

    <?php if ($canEdit): ?>
        <a href="/articles/<?= $article['id'] ?>/edit">Edit article</a>
    <?php endif; ?>
</main>
```

The keys passed to `Responses::view()` become template variables. Strings inside the article array are already HTML-escaped; do not apply `htmlspecialchars()` again. Calculate `canEdit` in a permission service before rendering. Hiding a link is not server-side authorization.

Set up `_header.php` and `_footer.php` as shown in [Static View Guide](/documentation/static-views). Use passed `$title`/`$description`; no metadata template is involved.

## Forms And Redirects

A form needs a POST handler as well as a GET page. Include the framework token:

```html
<form method="POST" action="/articles">
    <?= \CorianderCore\Core\Security\Csrf::input() ?>
    <input name="title" value="<?= $old['title'] ?? '' ?>">
    <button type="submit">Save article</button>
</form>
```

Prepare `old` and validation errors in the handler. Root CSRF middleware validates the token before the write handler. On successful persistence, return `Responses::redirect('/articles', 303)`. The browser then loads a GET page.

The [forum handler chapter](/guided-projects/forum/handlers) shows session flashes and return-to-topic navigation.

## Fragments And Repeated Rendering

```php
return Responses::view('articles/summary', ['article' => $article], layout: false);
```

This omits inherited layouts, useful when an endpoint intentionally returns only a fragment. Each render executes templates with current data. Keep declarations in autoloaded files so repeated rendering does not redeclare functions.

For explicit renderer injection, the equivalent API is `(new ViewRenderer())->response('articles/show', $data)` from `CorianderCore\Core\Router\ViewRenderer`; the old `render()` method is removed.

## Checkpoint

Visit `/articles/42`: expect the article and correct page title. Visit `/articles/999`: expect 404. Inspect [routes:list](/documentation/cli) if the first request is missing.

Use only fixed or allowlisted render names. Never pass raw visitor input as a template path.
