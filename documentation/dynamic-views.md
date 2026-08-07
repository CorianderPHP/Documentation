# Dynamic View Guide

A dynamic view is rendered by a controller with prepared data. Use this when the page depends on route parameters, database rows, permissions, validation errors, flash messages, or form state.

This example builds a page at `/articles/{id}`.

## Request Flow

```workflow
Request|The browser opens `/articles/42`.
Route|`public/routes.php` matches `/articles/{id}` and calls `ArticleController::show()`.
Controller|The controller reads the route id, loads the article, and prepares view data.
View|`public/public_views/articles/show/index.php` renders the prepared variables.
```

## Step 1: Create The View Folder

Create the view with the CLI:

```bash
php coriander make:view articles/show
```

This creates:

```structure
public/
  public_views/
    articles/
      show/
        index.php
        metadata.php
```

The render name is `articles/show`, because it maps to `public/public_views/articles/show/index.php`.

## Step 2: Create The Controller

Create a controller:

```bash
php coriander make:controller Article
```

Use `ViewRenderer` inside the controller and render the view with an array of prepared data:

```php
<?php
declare(strict_types=1);

namespace Controllers;

use CorianderCore\Core\Router\ViewRenderer;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class ArticleController
{
    private ViewRenderer $view;

    public function __construct()
    {
        $this->view = new ViewRenderer();
    }

    public function show(ServerRequestInterface $request): ?Response
    {
        $id = (int) $request->getAttribute('id');
        $article = $this->findArticle($id);

        if ($article === null) {
            return new Response(404, [], 'Article not found');
        }

        $this->view->render('articles/show', [
            'article' => $article,
            'relatedArticles' => $this->relatedArticles($id),
        ]);

        return null;
    }

    /**
     * @return array{id:int,title:string,body:string,author:string}|null
     */
    private function findArticle(int $id): ?array
    {
        $articles = [
            42 => [
                'id' => 42,
                'title' => 'Building dynamic views',
                'body' => 'Controllers prepare data before rendering templates.',
                'author' => 'Mira',
            ],
        ];

        return $articles[$id] ?? null;
    }

    /**
     * @return array<int,array{id:int,title:string}>
     */
    private function relatedArticles(int $id): array
    {
        return [
            ['id' => 7, 'title' => 'Routing basics'],
            ['id' => 13, 'title' => 'Controller structure'],
        ];
    }
}
```

In a real project, `findArticle()` and `relatedArticles()` should move into an app-owned repository or module under `src/Modules`.

## Step 3: Register The Dynamic Route

Add a route in `public/routes.php`, or in a route file loaded from it:

```php
use Controllers\ArticleController;

$articleController = new ArticleController();

$router->get('/articles/{id}', [$articleController, 'show']);
```

The `{id}` route parameter is available from the request:

```php
$id = (int) $request->getAttribute('id');
```

## Step 4: Use Data In The View

Edit `public/public_views/articles/show/index.php`:

```php
<article class="article">
    <p>Written by <?= $article['author'] ?></p>

    <h1><?= $article['title'] ?></h1>

    <div>
        <?= $article['body'] ?>
    </div>
</article>

<?php if ($relatedArticles !== []): ?>
    <aside>
        <h2>Related articles</h2>
        <ul>
            <?php foreach ($relatedArticles as $relatedArticle): ?>
                <li>
                    <a href="/articles/<?= $relatedArticle['id'] ?>">
                        <?= $relatedArticle['title'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>
<?php endif; ?>
```

The keys passed from the controller become variables in the view:

```php
[
    'article' => $article,
    'relatedArticles' => $relatedArticles,
]
```

becomes:

```php
$article
$relatedArticles
```

## Step 5: Add Metadata

Edit `public/public_views/articles/show/metadata.php`:

```php
<?php
$metadata = '
    <title>Article - CorianderPHP</title>
    <meta name="description" content="Read an article." />
';

$addViewInSitemap = false;
$sitemapPriority = 0.5;
```

Use generic metadata for parameterized pages unless your app has a dedicated dynamic metadata layer. Do not query the database from `metadata.php`.

## Dynamic Views With Forms

For forms, keep the same split:

- `GET /articles/create` renders an empty form.
- `POST /articles` validates input and writes through a service or repository.
- On success, redirect to the created page.
- On validation failure, re-render the form view with errors and old input.

Example render data for a failed form:

```php
$this->view->render('articles/create', [
    'errors' => $errors,
    'old' => $requestData,
]);
```

The view can then show field errors and preserve submitted values:

```php
<input name="title" value="<?= $old['title'] ?? '' ?>">

<?php if (isset($errors['title'])): ?>
    <p><?= $errors['title'] ?></p>
<?php endif; ?>
```

Use [Security](/documentation/security) for CSRF tokens and form safety.

## Output Safety

Variables passed to view templates are automatically escaped for HTML output to mitigate XSS attacks.

Avoid double escaping. If the framework already escaped a variable for output, do not escape the same value again unless you intentionally need a different context.

## Path Safety

View resolution only accepts normalized relative paths under `public/public_views`.

- Dot-segments such as `.` or `..` are rejected.
- Absolute paths are rejected.
- Null-byte path fragments are rejected.

This prevents path traversal and accidental inclusion of files outside the view root.

Shared templates now use an internally normalized requested-view value for metadata and asset resolution, instead of raw request path data.

## Best Practices

- Keep view logic minimal; handle business logic in controllers or services.
- Store assets under `public/assets` and reference them with absolute paths.
- Prefer dynamic views when the page needs prepared data, route parameters, validation errors, or permission flags.
- Use [Assets And Images](/documentation/assets) when rendering images with framework asset-path handling.

