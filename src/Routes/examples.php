<?php
declare(strict_types=1);

use Controllers\ExamplesController;
use ApiControllers\ShelterPlaygroundController;
use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

return static function (Router $router): void {
    $router->get('guided-projects', static fn () => (new ExamplesController())->index());
    $router->get('guided-projects/forum', static fn () => (new ExamplesController())->forum());
    $router->get('guided-projects/forum/search', static fn (ServerRequestInterface $request) => (new ExamplesController())->forumSearch($request));
    $router->get('guided-projects/forum/{slug:[A-Za-z0-9-\/]+}', static fn (ServerRequestInterface $request) => (new ExamplesController())->forum((string) $request->getAttribute('slug')));
    $router->get('guided-projects/shelter-api', static fn () => (new ExamplesController())->shelterApi());
    $router->get('guided-projects/shelter-api/search', static fn (ServerRequestInterface $request) => (new ExamplesController())->shelterApiSearch($request));
    $router->get('guided-projects/shelter-api/{slug:[A-Za-z0-9-\/]+}', static fn (ServerRequestInterface $request) => (new ExamplesController())->shelterApi((string) $request->getAttribute('slug')));

    $router->get('/api/playground/shelter/animals', static fn (ServerRequestInterface $request) => (new ShelterPlaygroundController())->animals($request));
    $router->get('/api/playground/shelter/animals/{id:[0-9]+}', static fn (ServerRequestInterface $request) => (new ShelterPlaygroundController())->animal($request));
    $router->post('/api/playground/shelter/animals', static fn (ServerRequestInterface $request) => (new ShelterPlaygroundController())->createAnimal($request));
    $router->patch('/api/playground/shelter/animals/{id:[0-9]+}', static fn (ServerRequestInterface $request) => (new ShelterPlaygroundController())->updateAnimal($request));
    $router->delete('/api/playground/shelter/animals/{id:[0-9]+}', static fn (ServerRequestInterface $request) => (new ShelterPlaygroundController())->deleteAnimal($request));
    $router->get('/api/playground/shelter/species', static fn () => (new ShelterPlaygroundController())->species());
    $router->get('/api/playground/shelter/shelters', static fn () => (new ShelterPlaygroundController())->shelters());

    $router->get('examples', static fn () => new Response(302, ['Location' => '/guided-projects'], ''));
    $router->get('examples/forum', static fn () => new Response(302, ['Location' => '/guided-projects/forum'], ''));
    $router->get('examples/forum/search', static fn (ServerRequestInterface $request) => new Response(302, ['Location' => '/guided-projects/forum/search?' . http_build_query($request->getQueryParams())], ''));
    $router->get('examples/forum/{slug:[A-Za-z0-9-\/]+}', static fn (ServerRequestInterface $request) => new Response(302, ['Location' => '/guided-projects/forum/' . (string) $request->getAttribute('slug')], ''));
    $router->get('examples/shelter-api', static fn () => new Response(302, ['Location' => '/guided-projects/shelter-api'], ''));
    $router->get('examples/shelter-api/search', static fn (ServerRequestInterface $request) => new Response(302, ['Location' => '/guided-projects/shelter-api/search?' . http_build_query($request->getQueryParams())], ''));
    $router->get('examples/shelter-api/{slug:[A-Za-z0-9-\/]+}', static fn (ServerRequestInterface $request) => new Response(302, ['Location' => '/guided-projects/shelter-api/' . (string) $request->getAttribute('slug')], ''));
};
