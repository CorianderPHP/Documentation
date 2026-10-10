# REST Routes

Use noun-based URLs and HTTP methods to express operations. CorianderPHP 0.3.0 discovers the following method files automatically.

## Endpoint Files

Relative to `src/Routes/api/shelter/`:

| File | Request | Action |
| --- | --- | --- |
| `animals/index.get.php` | GET /api/shelter/animals | index |
| `animals/index.post.php` | POST /api/shelter/animals | store |
| `animals/[id].get.php` | GET /api/shelter/animals/1 | show |
| `animals/[id].patch.php` | PATCH /api/shelter/animals/1 | update |
| `animals/[id].delete.php` | DELETE /api/shelter/animals/1 | destroy |
| `species.get.php` | GET /api/shelter/species | species |
| `shelters.get.php` | GET /api/shelter/shelters | shelters |

You are here in the flow:

```workflow
Request|The client sends a method and /api/shelter URL.
Method file|Discovery selects the matching .get, .post, .patch, or .delete file.
Action|The returned callable delegates to ShelterAnimalActions or ShelterLookupActions.
Service and repository|Validation and SQL produce data or a domain error.
JSON response|The action returns a PSR response, not a printed array.
```

## Collection GET And POST

Create `animals/index.get.php`:

```php
<?php
use App\Actions\ShelterAnimalActions;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    (new ShelterAnimalActions())->index($request);
```

Create `animals/index.post.php` with the same imports, returning `(new ShelterAnimalActions())->store($request)` from its callable. Keep one method per file; there is no route registration closure.

The [handler chapter](/guided-projects/shelter-api/handlers) supplies these methods.

## Detail, Update, And Delete

Create `animals/[id].get.php`:

```php
<?php
use App\Actions\ShelterAnimalActions;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    $id = (string) $request->getAttribute('id');
    if (!ctype_digit($id) || (int) $id < 1) {
        return Responses::json([
            'error' => ['code' => 'not_found', 'message' => 'Invalid animal id.'],
        ], 404);
    }
    return (new ShelterAnimalActions())->show($request);
};
```

For `[id].patch.php` and `[id].delete.php`, use the same guard and imports, but return `update($request)` and `destroy($request)` respectively.

`[id]` does not have a regex constraint. Validate it before casting: otherwise "1wrong" could accidentally select record 1. To reuse this guard across many routes, move detail files into `[id]/` and declare shared `_middleware.php` there.

## Lookup Files

Create `species.get.php`:

```php
<?php
use App\Actions\ShelterLookupActions;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    (new ShelterLookupActions())->species();
```

Create `shelters.get.php` the same way, delegating to `shelters()`.

## Matching And Protection

Root middleware is inherited automatically. Keep the narrowly scoped stateless CSRF exception described in [setup](/guided-projects/shelter-api/setup); do not disable all protection.

GET supplies HEAD fallback. The front controller's emitter suppresses the body for HEAD. A method without a file returns 405/Allow, not another action. Static named paths win over dynamic matches.

Before deployment, secure writes with authentication/authorization; these tutorial routes are not a production access policy.

## Checkpoint

Run `php coriander routes:list` and verify every endpoint. After actions/migrations are complete, request:

```http
GET /api/shelter/animals?species=cat&status=available
GET /api/shelter/animals/1
POST /api/shelter/animals
PATCH /api/shelter/animals/1
DELETE /api/shelter/animals/1
```

Check valid and missing ids, invalid ids, and an unsupported PUT. If a path is 404, check its filename; do not add a public/routes.php include.

Continue with [API Handlers](/guided-projects/shelter-api/handlers).
