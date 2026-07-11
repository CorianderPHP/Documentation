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
