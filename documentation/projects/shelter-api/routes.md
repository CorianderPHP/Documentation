# REST Routes

The route file should describe the HTTP surface and delegate work to controllers. Keep database queries and validation out of the route file.

## Route file

You created `src/Routes/api/shelter.php` in the project structure step. Now replace its content with the API routes:

```php
<?php
declare(strict_types=1);

use ApiControllers\ShelterAnimalController;
use ApiControllers\ShelterLookupController;
use CorianderCore\Core\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

return static function (Router $router): void {
    $router->get('/api/shelter/animals', static fn (ServerRequestInterface $request) => (new ShelterAnimalController())->index($request));
    $router->get('/api/shelter/animals/{id:[0-9]+}', static fn (ServerRequestInterface $request) => (new ShelterAnimalController())->show($request));
    $router->post('/api/shelter/animals', static fn (ServerRequestInterface $request) => (new ShelterAnimalController())->store($request));
    $router->patch('/api/shelter/animals/{id:[0-9]+}', static fn (ServerRequestInterface $request) => (new ShelterAnimalController())->update($request));
    $router->delete('/api/shelter/animals/{id:[0-9]+}', static fn (ServerRequestInterface $request) => (new ShelterAnimalController())->destroy($request));

    $router->get('/api/shelter/species', static fn () => (new ShelterLookupController())->species());
    $router->get('/api/shelter/shelters', static fn () => (new ShelterLookupController())->shelters());
};
```

## Route responsibilities

- Use nouns in paths: `animals`, `species`, and `shelters`.
- Use HTTP methods for actions: `GET`, `POST`, `PATCH`, and `DELETE`.
- Use path parameters for identifiers.
- Use query parameters for filters.
- Return PSR-7 responses from controllers.

## Example requests

```http
GET /api/shelter/animals?species=cat&status=available
GET /api/shelter/animals?search=milo
GET /api/shelter/animals/1
POST /api/shelter/animals
PATCH /api/shelter/animals/1
DELETE /api/shelter/animals/1
```

## Common mistake

Do not build one route like `/api/shelter/animals/action/delete`. That makes permissions, tests, documentation, and client code harder to reason about. Let the method express the action.
