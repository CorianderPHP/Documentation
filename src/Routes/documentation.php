<?php
declare(strict_types=1);

use Controllers\DocsController;
use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

return static function (Router $router): void {
    $router->get('documentation', static fn () => (new DocsController())->index());
    $router->get('documentation/search', static fn (ServerRequestInterface $request) => (new DocsController())->search($request));
    $router->get('documentation/forum-project', static fn () => new Response(302, ['Location' => '/guided-projects/forum'], ''));
    $router->get('documentation/projects/forum', static fn () => new Response(302, ['Location' => '/guided-projects/forum'], ''));
    $router->get('documentation/projects/forum/{slug:[A-Za-z0-9-\/]+}', static fn (ServerRequestInterface $request) => new Response(302, ['Location' => '/guided-projects/forum/' . (string) $request->getAttribute('slug')], ''));
    $router->get('documentation/{slug:[A-Za-z0-9-\/]+}', static fn (ServerRequestInterface $request) => (new DocsController())->show((string) $request->getAttribute('slug')));

    $router->get('docs', static fn () => new Response(302, ['Location' => '/documentation'], ''));
    $router->get('docs/search', static function (ServerRequestInterface $request): Response {
        $query = http_build_query($request->getQueryParams());
        return new Response(302, ['Location' => '/documentation/search' . ($query === '' ? '' : '?' . $query)], '');
    });
    $router->get('docs/{slug:[A-Za-z0-9-\/]+}', static fn (ServerRequestInterface $request) => new Response(302, ['Location' => '/documentation/' . (string) $request->getAttribute('slug')], ''));
};
