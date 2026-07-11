<?php
declare(strict_types=1);

use Controllers\DocsController;
use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

return static function (Router $router): void {
    $router->get('docs', static fn () => (new DocsController())->index());
    $router->get('docs/search', static fn (ServerRequestInterface $request) => (new DocsController())->search($request));
    $router->get('docs/forum-project', static fn () => new Response(302, ['Location' => '/guided-projects/forum'], ''));
    $router->get('docs/projects/forum', static fn () => new Response(302, ['Location' => '/guided-projects/forum'], ''));
    $router->get('docs/projects/forum/{slug:[A-Za-z0-9-\/]+}', static fn (ServerRequestInterface $request) => new Response(302, ['Location' => '/guided-projects/forum/' . (string) $request->getAttribute('slug')], ''));
    $router->get('docs/{slug:[A-Za-z0-9-\/]+}', static fn (ServerRequestInterface $request) => (new DocsController())->show((string) $request->getAttribute('slug')));
};
