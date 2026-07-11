<?php
declare(strict_types=1);

use Controllers\ForumDemoController;
use CorianderCore\Core\Router\Router;
use Middleware\ForumDemoAdminMiddleware;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

return static function (Router $router): void {
    $router->get('forum-demo', static fn (ServerRequestInterface $request) => (new ForumDemoController())->index($request));
    $router->get('forum-demo/login', static fn () => (new ForumDemoController())->login());
    $router->post('forum-demo/login', static fn (ServerRequestInterface $request) => (new ForumDemoController())->authenticate($request));
    $router->post('forum-demo/logout', static fn () => (new ForumDemoController())->logout());
    $router->get('forum-demo/topics', static fn () => (new ForumDemoController())->topics());
    $router->post('forum-demo/topics', static fn (ServerRequestInterface $request) => (new ForumDemoController())->storeTopic($request));
    $router->get('forum-demo/topics/{id:[0-9]+}', static fn (ServerRequestInterface $request) => (new ForumDemoController())->showTopic((string) $request->getAttribute('id')));
    $router->get('forum-demo/topics/{id:[0-9]+}/replies', static fn (ServerRequestInterface $request) => new Response(302, ['Location' => '/forum-demo/topics/' . (string) $request->getAttribute('id')], ''));
    $router->post('forum-demo/topics/{id:[0-9]+}/replies', static fn (ServerRequestInterface $request) => (new ForumDemoController())->storeReply($request, (string) $request->getAttribute('id')));

    $router->group('forum-demo/admin', [new ForumDemoAdminMiddleware()], static function (Router $admin): void {
        $admin->get('', static fn () => (new ForumDemoController())->admin());
        $admin->get('users', static fn () => (new ForumDemoController())->adminUsers());
        $admin->post('users', static fn (ServerRequestInterface $request) => (new ForumDemoController())->updateUserRole($request));
        $admin->post('topics', static fn (ServerRequestInterface $request) => (new ForumDemoController())->moderateTopic($request));
        $admin->post('replies', static fn (ServerRequestInterface $request) => (new ForumDemoController())->moderateReply($request));
    });
};
