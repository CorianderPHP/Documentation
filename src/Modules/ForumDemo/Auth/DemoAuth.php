<?php
declare(strict_types=1);

namespace Modules\ForumDemo\Auth;

use Modules\ForumDemo\Data\DemoUserRepository;

final class DemoAuth
{
    private const SESSION_KEY = 'forum_demo_user_id';

    public function __construct(private readonly DemoUserRepository $users = new DemoUserRepository())
    {
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);
        if ($user === null || !hash_equals($user['password'], $password)) {
            return false;
        }

        $_SESSION[self::SESSION_KEY] = $user['id'];
        return true;
    }

    public function loginAs(string $role): bool
    {
        $user = $this->users->findByRole($role);
        if ($user === null) {
            return false;
        }

        $_SESSION[self::SESSION_KEY] = $user['id'];
        return true;
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * @return array{id:int,name:string,email:string,role:string,password:string}|null
     */
    public function currentUser(): ?array
    {
        $id = $_SESSION[self::SESSION_KEY] ?? null;
        return is_int($id) ? $this->users->find($id) : null;
    }
}
