<?php
declare(strict_types=1);

namespace Modules\ForumDemo\Writes;

use Modules\ForumDemo\Permissions\DemoPermissionService;

final class DemoWriteGuard
{
    public function __construct(private readonly DemoPermissionService $permissions = new DemoPermissionService())
    {
    }

    /**
     * @param array{id:int,name:string,email:string,role:string,password:string}|null $user
     * @param array<string,mixed> $payload
     * @return array{ok:bool,demo:bool,status:int,message:string,action:string}
     */
    public function fakeWrite(?array $user, string $ability, string $action, array $payload = []): array
    {
        if (!$this->permissions->can($user, $ability)) {
            return [
                'ok' => false,
                'demo' => true,
                'status' => 403,
                'message' => 'Permission denied for this demo account.',
                'action' => $action,
            ];
        }

        if (!$this->hasEnoughInput($payload)) {
            return [
                'ok' => false,
                'demo' => true,
                'status' => 422,
                'message' => 'Demo validation failed. Add a title or message before submitting.',
                'action' => $action,
            ];
        }

        return [
            'ok' => true,
            'demo' => true,
            'status' => 200,
            'message' => 'Demo mode: the action passed validation but was not saved.',
            'action' => $action,
        ];
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function hasEnoughInput(array $payload): bool
    {
        if ($payload === []) {
            return true;
        }

        foreach (['title', 'body', 'name'] as $field) {
            $value = $payload[$field] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return true;
            }
        }

        return false;
    }
}
