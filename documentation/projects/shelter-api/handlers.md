# API Request Handlers

Each discovered method file delegates to an ordinary App action class. The actions read parsed request data, call AnimalService, catch domain errors, and return JSON. Database access stays in the repository.

## Response Helper And Domain Errors

Create the files below. `ApiJson` delegates encoding to the framework's `Responses::json()` and keeps our success/error envelopes consistent. The exception classes are app-owned, not framework APIs.

## src/Modules/ShelterApi/ApiJson.php

```php
<?php
declare(strict_types=1);

namespace App\Modules\ShelterApi;

use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;

/*
 * Response helper:
 * every API action returns JSON through this class so success and error
 * responses stay predictable for clients and tests.
 */
final class ApiJson
{
    public static function response(array $payload, int $status = 200): ResponseInterface
    {
        return Responses::json($payload, $status);
    }

    public static function error(string $code, string $message, int $status, array $fields = []): ResponseInterface
    {
        $error = ['code' => $code, 'message' => $message];
        if ($fields !== []) {
            $error['fields'] = $fields;
        }

        return self::response(['error' => $error], $status);
    }
}
```

## src/Actions/ShelterAnimalActions.php

```php
<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\ShelterApi\AnimalService;
use App\Modules\ShelterApi\ApiJson;
use App\Modules\ShelterApi\NotFoundException;
use App\Modules\ShelterApi\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/*
 * Route entrypoint for /api/shelter/animals.
 * These actions translate HTTP details into service calls and return JSON.
 * It does not build SQL or duplicate validation rules.
 */
final class ShelterAnimalActions
{
    public function __construct(private readonly AnimalService $animals = new AnimalService())
    {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return ApiJson::response(['data' => $this->animals->list($request->getQueryParams())]);
    }

    public function show(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return ApiJson::response(['data' => $this->animals->find((int) $request->getAttribute('id'))]);
        } catch (NotFoundException $exception) {
            return ApiJson::error('not_found', $exception->getMessage(), 404);
        }
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return ApiJson::response(['data' => $this->animals->create($this->jsonBody($request))], 201);
        } catch (ValidationException $exception) {
            return ApiJson::error('validation_failed', 'The request body is invalid.', 422, $exception->fields());
        }
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return ApiJson::response(['data' => $this->animals->update((int) $request->getAttribute('id'), $this->jsonBody($request))]);
        } catch (NotFoundException $exception) {
            return ApiJson::error('not_found', $exception->getMessage(), 404);
        } catch (ValidationException $exception) {
            return ApiJson::error('validation_failed', 'The request body is invalid.', 422, $exception->fields());
        }
    }

    public function destroy(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->animals->archive((int) $request->getAttribute('id'));
            return ApiJson::response(['data' => ['deleted' => true]]);
        } catch (NotFoundException $exception) {
            return ApiJson::error('not_found', $exception->getMessage(), 404);
        }
    }

    private function jsonBody(ServerRequestInterface $request): array
    {
        // RequestFactory parses JSON once before routing; services receive plain data.
        return (array) $request->getParsedBody();
    }
}
```

## src/Actions/ShelterLookupActions.php

```php
<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\ShelterApi\AnimalService;
use App\Modules\ShelterApi\ApiJson;
use Psr\Http\Message\ResponseInterface;

final class ShelterLookupActions
{
    public function species(): ResponseInterface
    {
        return ApiJson::response(['data' => (new AnimalService())->species()]);
    }

    public function shelters(): ResponseInterface
    {
        return ApiJson::response(['data' => (new AnimalService())->shelters()]);
    }
}
```

## src/Modules/ShelterApi/NotFoundException.php

```php
<?php
declare(strict_types=1);

namespace App\Modules\ShelterApi;

use RuntimeException;

final class NotFoundException extends RuntimeException
{
}
```

## src/Modules/ShelterApi/ValidationException.php

```php
<?php
declare(strict_types=1);

namespace App\Modules\ShelterApi;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    public function __construct(private readonly array $fields)
    {
        parent::__construct('The request body is invalid.');
    }

    public function fields(): array
    {
        return $this->fields;
    }
}
```

## Connect And Verify

These classes are autoloaded through `"App\\": "src/"`. Use the method files from [REST Routes](/guided-projects/shelter-api/routes); classes alone do not expose endpoints.

`RequestFactory` parses JSON once before routing. A malformed body produces 400 before these methods; valid but incomplete data produces 422 from validation. Create returns 201, missing/archived records return 404, and archive leaves the database row for audit.

Run the migration, then fetch /api/shelter/animals/1. Create an animal, PATCH its status, and DELETE it. Check the actual database between requests; this local API persists writes, unlike the public playground.

```workflow
Method file|Delegates the request to ShelterAnimalActions.
Action|Reads query/path/parsed body and chooses a JSON response.
AnimalService|Validates the operation and calls the repository.
Repository|Uses parameterized SQL against SQLite.
ApiJson|Formats success or a domain error through Responses::json().
```

Continue with [Filtering And Validation](/guided-projects/shelter-api/filtering-validation).
