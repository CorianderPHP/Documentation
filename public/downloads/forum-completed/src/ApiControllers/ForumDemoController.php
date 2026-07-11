<?php
declare(strict_types=1);

namespace ApiControllers;

use Modules\ForumDemo\Auth\DemoAuth;
use Modules\ForumDemo\Permissions\DemoPermissionService;
use Modules\ForumDemo\Writes\DemoWriteGuard;

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

    public function post_topic(): array
    {
        return $this->fakeWrite('topic.create', 'create topic');
    }

    public function post_reply(): array
    {
        return $this->fakeWrite('reply.create', 'create reply');
    }

    public function post_moderate(): array
    {
        return $this->fakeWrite('reply.moderate', 'moderate reply');
    }

    /**
     * @return array<string,mixed>
     */
    private function fakeWrite(string $ability, string $action): array
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        $payload = is_array($payload) ? $payload : $_POST;
        return $this->writeGuard->fakeWrite($this->auth->currentUser(), $ability, $action, $payload);
    }
}
