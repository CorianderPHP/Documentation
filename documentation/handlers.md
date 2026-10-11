# Request Handlers

A request handler is the callable returned by a file in `src/Views`. It reads input, calls application logic, and returns a response. CorianderPHP 0.4.1 does not discover controller methods or provide `make:controller`.

## A Complete JSON Handler

Create `src/Views/greeting.get.php`:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    $query = $request->getQueryParams();
    $name = $query['name'] ?? 'visitor';
    if (!is_string($name)) {
        return Responses::json(['error' => 'name must be text.'], 422);
    }

    return Responses::json(['message' => 'Hello ' . $name]);
};
```

Request `/greeting?name=Sam`. `Responses::json()` sets the JSON content type and encodes the data. Arrays from query or form input are possible, so check types before treating a value as text.

## Choose A Response

```php
Responses::html('<p>Ready</p>');
Responses::json(['data' => $items], 200);
Responses::view('articles/show', ['metadata' => ['title' => 'Article'], 'article' => $article]);
Responses::redirect('/articles', 303);
```

All four return a `ResponseInterface`. A callable handler must return that response; printing output, returning `null`, or returning an array directly is an error. Direct GET pages are a different contract: they may output PHP/HTML without returning a handler. You may also return your own PSR response.

The default redirect status is 303. Use it after a successful form POST so refresh/back navigation returns to a GET page instead of resubmitting the form.

## Incoming Request URLs

`RequestFactory::fromGlobals()` now creates a complete incoming URI when host information exists. The router still matches its path, not its origin:

```php
$path = $request->getUri()->getPath();        // /articles/42
$query = $request->getQueryParams();          // Parsed query values
$incomingUrl = (string) $request->getUri();   // https://example.com/articles/42?language=en
```

Use `getPath()` for path comparisons and middleware gates. Casting the whole URI to a string is no longer a reliable path-only comparison. Encoded paths and queries are preserved; hostless injected requests may still have relative URIs.

Incoming `Host` headers take precedence over server fallback values. HTTPS detection follows `TRUSTED_PROXIES`; forwarded hosts are ignored, and internal proxy listener ports do not replace the incoming authority. Invalid authorities raise a `BadRequestException`, which the starter emits as 400.

The host is visitor-controlled input, not a trusted canonical domain. For emails, password-reset URLs, or fixed-domain links, use your configured origin (for example `PROJECT_URL`) and an app-controlled path. Do not trust the incoming origin simply because it parses successfully.

## Read Forms And JSON

The starter constructs requests with `RequestFactory::fromGlobals()`. Use the parsed body instead of decoding the raw stream again:

```php
$payload = (array) $request->getParsedBody();
$title = $payload['title'] ?? '';
if (!is_string($title) || trim($title) === '') {
    return Responses::json(['error' => 'A title is required.'], 422);
}
```

Malformed JSON fails with 400 during request creation. Declared or actual oversized bodies fail with 413 before application JSON parsing. Valid JSON still needs business validation; an array is not necessarily the object your endpoint expects.

## Reuse App-Owned Action Classes

A small route can contain its whole handler. When several routes share dependencies, create an ordinary class. `Actions` is an application convention used by this website, not a framework-managed folder or special discovery feature.

With Composer's `"App\\": "src/"` mapping, create `src/Actions/ArticleActions.php`:

```php
<?php
declare(strict_types=1);

namespace App\Actions;

use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ArticleActions
{
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        $id = (string) $request->getAttribute('id');
        if (!ctype_digit($id) || (int) $id < 1) {
            return Responses::html('Article not found.', 404);
        }

        // Replace sample data with a repository call when adding persistence.
        $article = ['id' => (int) $id, 'title' => 'First article'];
        return Responses::view('articles/show', [
            'metadata' => ['title' => $article['title']],
            'article' => $article,
        ]);
    }
}
```

Then `src/Views/articles/[id].get.php` returns the method:

```php
<?php
use App\Actions\ArticleActions;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    (new ArticleActions())->show($request);
```

Run `composer dump-autoload` after adding/changing Composer mappings. Creating an action class alone does not expose its methods; only route files define URLs.

Keep SQL, reusable validation, and permissions in [app modules](/documentation/modules). See [Dynamic View Guide](/documentation/dynamic-views) for the matching template and [Shelter API handlers](/guided-projects/shelter-api/handlers) for a persisted API.
