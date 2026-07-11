<?php
declare(strict_types=1);

namespace Modules\ForumDemo\Data;

final class DemoUserRepository
{
    /**
     * @return array<int,array{id:int,name:string,email:string,role:string,password:string}>
     */
    public function all(): array
    {
        return [
            ['id' => 1, 'name' => 'Mira Admin', 'email' => 'admin@example.com', 'role' => 'admin', 'password' => 'demo-admin'],
            ['id' => 2, 'name' => 'Sam Member', 'email' => 'user@example.com', 'role' => 'member', 'password' => 'demo-user'],
            ['id' => 3, 'name' => 'Nora Moderator', 'email' => 'moderator@example.com', 'role' => 'moderator', 'password' => 'demo-moderator'],
        ];
    }

    /**
     * @return array{id:int,name:string,email:string,role:string,password:string}|null
     */
    public function find(int $id): ?array
    {
        foreach ($this->all() as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @return array{id:int,name:string,email:string,role:string,password:string}|null
     */
    public function findByEmail(string $email): ?array
    {
        foreach ($this->all() as $user) {
            if (strcasecmp($user['email'], trim($email)) === 0) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @return array{id:int,name:string,email:string,role:string,password:string}|null
     */
    public function findByRole(string $role): ?array
    {
        foreach ($this->all() as $user) {
            if ($user['role'] === $role) {
                return $user;
            }
        }

        return null;
    }
}
