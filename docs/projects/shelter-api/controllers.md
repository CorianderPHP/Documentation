# API Controllers

Controllers should stay thin. They read request data, call the service layer, and return JSON. The service and repository own business rules and database access.

## JSON helper

Create `src/Modules/ShelterApi/ApiJson.php`:

```php
<?php
declare(strict_types=1);

namespace Modules\ShelterApi;

use Nyholm\Psr7\Response;

final class ApiJson
{
    public static function response(array $payload, int $status = 200): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'application/json; charset=utf-8'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }
}
```

## Animal controller

You created `src/ApiControllers/ShelterAnimalController.php` in the project structure step. Replace its generated body with this implementation:

```php
<?php
declare(strict_types=1);

namespace ApiControllers;

use Modules\ShelterApi\AnimalService;
use Modules\ShelterApi\ApiJson;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ShelterAnimalController
{
    public function __construct(private readonly AnimalService $animals = new AnimalService())
    {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return ApiJson::response([
            'data' => $this->animals->list($request->getQueryParams()),
        ]);
    }

    public function show(ServerRequestInterface $request): ResponseInterface
    {
        $animal = $this->animals->find((int) $request->getAttribute('id'));
        return $animal === null
            ? ApiJson::response(['error' => ['code' => 'not_found', 'message' => 'Animal not found.']], 404)
            : ApiJson::response(['data' => $animal]);
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $animal = $this->animals->create($this->jsonBody($request));
        return ApiJson::response(['data' => $animal], 201);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $animal = $this->animals->update((int) $request->getAttribute('id'), $this->jsonBody($request));
        return ApiJson::response(['data' => $animal]);
    }

    public function destroy(ServerRequestInterface $request): ResponseInterface
    {
        $this->animals->archive((int) $request->getAttribute('id'));
        return ApiJson::response(['data' => ['deleted' => true]]);
    }

    private function jsonBody(ServerRequestInterface $request): array
    {
        $body = json_decode((string) $request->getBody(), true);
        return is_array($body) ? $body : [];
    }
}
```

## Lookup controller

You also created `src/ApiControllers/ShelterLookupController.php` in the project structure step. Keep it read-only:

```php
<?php
declare(strict_types=1);

namespace ApiControllers;

use Modules\ShelterApi\AnimalService;
use Modules\ShelterApi\ApiJson;
use Psr\Http\Message\ResponseInterface;

final class ShelterLookupController
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

## Why services matter

If controllers directly query SQL, every endpoint starts duplicating rules. The service gives you one place for allowed statuses, validation errors, soft deletes, and response shaping.
