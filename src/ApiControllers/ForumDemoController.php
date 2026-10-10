<?php
declare(strict_types=1);

namespace ApiControllers;

use Modules\ForumDemo\Auth\DemoAuth;
use Modules\ForumDemo\Permissions\DemoPermissionService;
use Modules\ForumDemo\Writes\DemoWriteGuard;
use CorianderCore\Core\Bootstrap\SessionBootstrap;
use CorianderCore\Core\Security\Csrf;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class ForumDemoController
{
    private DemoAuth $auth;
    private DemoWriteGuard $writeGuard;

    public function __construct()
    {
        $permissions = new DemoPermissionService();
        $this->auth = new DemoAuth();
        $this->writeGuard = new DemoWriteGuard($permissions);
    }

    public function post_topic(ServerRequestInterface $request): Response
    {
        return $this->fakeWrite($request, 'topic.create', 'create topic');
    }

    public function post_reply(ServerRequestInterface $request): Response
    {
        return $this->fakeWrite($request, 'reply.create', 'create reply');
    }

    public function post_moderate(ServerRequestInterface $request): Response
    {
        return $this->fakeWrite($request, 'reply.moderate', 'moderate reply');
    }

    private function fakeWrite(ServerRequestInterface $request, string $ability, string $action): Response
    {
        // These API routes use the forum login cookie, unlike a stateless API.
        SessionBootstrap::start();
        if (!Csrf::validate($request->getHeaderLine('X-CSRF-Token'))) {
            return $this->json(['ok' => false, 'message' => 'Invalid CSRF token.'], 403);
        }

        $body = (string) $request->getBody();
        try {
            $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json(['ok' => false, 'message' => 'Invalid JSON body.'], 400);
        }
        if (!is_array($payload) || !str_starts_with(ltrim($body), '{')) {
            return $this->json(['ok' => false, 'message' => 'Send a JSON object.'], 400);
        }

        $result = $this->writeGuard->fakeWrite($this->auth->currentUser(), $ability, $action, $payload);
        return $this->json($result, $result['status']);
    }

    private function json(array $payload, int $status): Response
    {
        return new Response($status, ['Content-Type' => 'application/json; charset=utf-8'], json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
