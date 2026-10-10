<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\ForumDemo\Auth\DemoAuth;
use App\Modules\ForumDemo\Permissions\DemoPermissionService;
use App\Modules\ForumDemo\Writes\DemoWriteGuard;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ForumApiActions
{
    private DemoAuth $auth;
    private DemoWriteGuard $writeGuard;

    public function __construct()
    {
        $permissions = new DemoPermissionService();
        $this->auth = new DemoAuth();
        $this->writeGuard = new DemoWriteGuard($permissions);
    }

    public function post_topic(ServerRequestInterface $request): ResponseInterface
    {
        return $this->fakeWrite($request, 'topic.create', 'create topic');
    }

    public function post_reply(ServerRequestInterface $request): ResponseInterface
    {
        return $this->fakeWrite($request, 'reply.create', 'create reply');
    }

    public function post_moderate(ServerRequestInterface $request): ResponseInterface
    {
        return $this->fakeWrite($request, 'reply.moderate', 'moderate reply');
    }

    private function fakeWrite(ServerRequestInterface $request, string $ability, string $action): ResponseInterface
    {
        // Root middleware checks body CSRF tokens; RequestFactory already parsed JSON.
        $payload = (array) $request->getParsedBody();
        $result = $this->writeGuard->fakeWrite($this->auth->currentUser(), $ability, $action, $payload);
        return Responses::json($result, $result['status']);
    }
}
