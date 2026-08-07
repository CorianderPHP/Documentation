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

## Static Views And Dynamic Views

A static view is mostly fixed markup. It can be enough for a home page, contact page, or legal page.

A dynamic view is rendered by a controller with prepared data. Use this when the page depends on route parameters, database rows, permissions, validation errors, flash messages, or form state.

The responsibility split should stay simple:

- Routes decide which controller action handles the URL.
- Controllers read request data, call app-owned modules or repositories, and prepare view data.
- Views display the prepared data.
- Views should not run database queries or own permission decisions.

## Dynamic View From Scratch

This example builds a page at `/articles/{id}`.

The request flow will be:

```structure
/articles/42
  public/routes.php
  ArticleController::show()
  public/public_views/articles/show/index.php
```

### Step 1: Create The View Folder

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

### Step 2: Create The Controller

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

### Step 3: Register The Dynamic Route

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

### Step 4: Use Data In The View

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

### Step 5: Add Metadata

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

### Step 6: Dynamic Views With Forms

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
- Prefer dynamic views when the page needs prepared data, route parameters, validation errors, or permission flags.
