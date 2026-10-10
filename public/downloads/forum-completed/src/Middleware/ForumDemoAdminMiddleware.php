<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Modules\ForumDemo\Auth\DemoAuth;
use App\Modules\ForumDemo\Permissions\DemoPermissionService;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ForumDemoAdminMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = (new DemoAuth())->currentUser();
        if (!(new DemoPermissionService())->can($user, 'admin.view')) {
            return new Response(302, ['Location' => '/forum-demo/login'], '');
        }

        return $handler->handle($request);
    }
}
