# Request Handlers

A request handler is the callable returned by a file in `src/Routes`. It reads input, calls application logic, and returns a response. CorianderPHP 0.3.0 does not discover controller methods or provide `make:controller`.

## A Complete JSON Handler

Create `src/Routes/greeting.get.php`:

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
Responses::view('articles/show', ['title' => 'Article', 'article' => $article]);
Responses::redirect('/articles', 303);
```

All four return a `ResponseInterface`. A route must return that response; printing HTML, returning `null`, or returning an array directly is an error. You may also return your own PSR response.

The default redirect status is 303. Use it after a successful form POST so refresh/back navigation returns to a GET page instead of resubmitting the form.

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
            'title' => $article['title'],
            'article' => $article,
        ]);
    }
}
```

Then `src/Routes/articles/[id].get.php` returns the method:

```php
<?php
use App\Actions\ArticleActions;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    (new ArticleActions())->show($request);
```

Run `composer dump-autoload` after adding/changing Composer mappings. Creating an action class alone does not expose its methods; only route files define URLs.

Keep SQL, reusable validation, and permissions in [app modules](/documentation/modules). See [Dynamic View Guide](/documentation/dynamic-views) for the matching template and [Shelter API handlers](/guided-projects/shelter-api/handlers) for a persisted API.
