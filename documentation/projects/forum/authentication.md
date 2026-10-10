# Demo Authentication

The demo uses fixed accounts so visitors can test permissions without creating accounts.

## Goal

Create session-based demo authentication for an admin account and a member account.

## File Created

```structure
src/Modules/ForumDemo/Auth/DemoAuth.php
```

## Step: Create The Auth Module

```php
<?php
declare(strict_types=1);

namespace App\Modules\ForumDemo\Auth;

use App\Modules\ForumDemo\Data\UserRepository;
use CorianderCore\Core\Bootstrap\SessionBootstrap;

final class DemoAuth
{
    private const SESSION_KEY = 'forum_demo_user_id';

    public function __construct(private readonly UserRepository $users = new UserRepository())
    {
    }
}
```

The auth module depends on the user repository instead of duplicating account data.

## Step: Add Login By Credentials

```php
public function login(string $email, string $password): bool
{
    SessionBootstrap::start();
    $user = $this->users->findByEmail($email);
    if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION[self::SESSION_KEY] = (int) $user['id'];
    return true;
}
```

`password_verify` matches the hashes seeded in the SQLite data model. Do not compare plain text passwords.

## Step: Add Quick Role Login

The demo can also provide buttons for "Use Admin" and "Use Member".

```php
public function loginAs(string $role): bool
{
    SessionBootstrap::start();
    $user = $this->users->findByRole($role);
    if ($user === null) {
        return false;
    }

    $_SESSION[self::SESSION_KEY] = $user['id'];
    return true;
}
```

This is useful for documentation because visitors can test roles quickly.

## Step: Add Logout And Current User

```php
public function logout(): void
{
    SessionBootstrap::start();
    unset($_SESSION[self::SESSION_KEY]);
}

public function currentUser(): ?array
{
    SessionBootstrap::start();
    $id = $_SESSION[self::SESSION_KEY] ?? null;
    return is_int($id) ? $this->users->find($id) : null;
}
```

CorianderPHP 0.3.0 does not start sessions eagerly. Every method must start/resume the session before accessing `$_SESSION`; otherwise existing login state can appear missing. See [Sessions](/documentation/sessions).

Quick role login is only for a protected educational demo. Never ship it as production authentication. The local project verifies seeded password hashes; the downloaded public-demo reference instead uses fixed in-memory accounts and does not provide production authentication.

## Step: Build The Login Form

Use framework CSRF tokens in web forms.

```html
<form method="POST" action="/forum-demo/login">
    <?= \CorianderCore\Core\Security\Csrf::input() ?>
    <input name="email" value="admin@example.com">
    <input name="password" type="password" value="demo-admin">
    <button>Log in</button>
</form>
```

## Checkpoint

Open [/forum-demo/login](/forum-demo/login), log in as admin, then open [/forum-demo/admin](/forum-demo/admin).

## Common Mistakes

- Using GET for login or logout actions.
- Forgetting CSRF tokens on web POST forms.
- Letting the view decide whether a user is authenticated instead of using `DemoAuth`.

## Next

Continue with [Permissions](/guided-projects/forum/permissions).
