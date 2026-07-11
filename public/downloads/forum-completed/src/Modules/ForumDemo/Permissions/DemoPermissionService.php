<?php
declare(strict_types=1);

namespace Modules\ForumDemo\Permissions;

final class DemoPermissionService
{
    /**
     * @param array{id:int,name:string,email:string,role:string,password:string}|null $user
     */
    public function can(?array $user, string $ability): bool
    {
        $role = $user['role'] ?? 'guest';

        return match ($ability) {
            'topic.create', 'reply.create' => in_array($role, ['member', 'moderator', 'admin'], true),
            'topic.lock', 'reply.moderate' => in_array($role, ['moderator', 'admin'], true),
            'category.manage', 'user.manage', 'admin.view' => $role === 'admin',
            default => false,
        };
    }

    /**
     * @param array{id:int,name:string,email:string,role:string,password:string}|null $user
     * @return array<string,bool>
     */
    public function matrix(?array $user): array
    {
        $abilities = ['topic.create', 'reply.create', 'topic.lock', 'reply.moderate', 'category.manage', 'user.manage', 'admin.view'];
        $matrix = [];
        foreach ($abilities as $ability) {
            $matrix[$ability] = $this->can($user, $ability);
        }

        return $matrix;
    }
}
