# Authentication, Sessions And Flash Messages

CorianderPHP 0.4.1 configures session cookies during bootstrap but does not start a session for every page. A public GET page can remain session-free.

## Start Before Reading Or Writing

```php
use CorianderCore\Core\Bootstrap\SessionBootstrap;

SessionBootstrap::start();
$userId = $_SESSION['user_id'] ?? null;
```

Call this before authentication, permission checks based on session state, or flashes. Reading `$_SESSION` without starting/resuming the session can make a logged-in user appear logged out.

The helper can be called again by code that also needs a session. Configure cookies through the starter before starting the session; do not duplicate raw `session_start()` calls throughout the app.

## Login And Logout

The framework supplies session startup and CSRF protection, not a user-registration system or a built-in auth service. Your application owns password verification, current-user lookup, permissions, and logout policy. Authentication identifies the user; authorization checks whether that user may perform an action.

```workflow
Show the form|A GET page renders a login form with Csrf::input().
Verify the POST|CSRF middleware validates the request; your handler validates input and verifies a stored password hash.
Store identity|Start the session, rotate its ID, and store the database user ID, never a submitted role.
Protect requests|Directory middleware starts the session and checks login before dispatching protected handlers.
Authorize actions|Reload trusted user data and check the required permission before reading sensitive data or writing.
Log out|A CSRF-protected POST removes login state and redirects to a GET page.
```

### Prerequisites

The following files form a small session-login example for an installed project. Keep the starter's CSRF middleware active. Configure a database with a `users` table containing `id`, a unique `email`, and `password_hash`; these are application columns, not framework-created data. Store hashes made by PHP's `password_hash()`, never plaintext passwords. The [forum data model](/guided-projects/forum/data-model) provides a complete migration and seed example.

This is not a complete production authentication product: add login throttling, account lifecycle/recovery, session expiry, and appropriate permission checks for your app. Never copy the forum's quick role-login buttons into a real application.

### Show A Login Form

Create `src/Views/login.get.php` as a complete direct GET page:

```html
<form method="POST" action="/login">
    <?= \CorianderCore\Core\Security\Csrf::input() ?>
    <label>Email <input name="email" type="email" autocomplete="username" required></label>
    <label>Password <input name="password" type="password" autocomplete="current-password" required></label>
    <button type="submit">Log in</button>
</form>
```

### Verify Credentials

Create `src/Views/login.post.php` with this complete handler:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Bootstrap\SessionBootstrap;
use CorianderCore\Core\Database\SQLManager;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    $body = (array) $request->getParsedBody();
    $email = $body['email'] ?? null;
    $password = $body['password'] ?? null;
    if (!is_string($email) || !is_string($password)
        || strlen($email) > 254 || strlen($password) > 1024
        || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return Responses::html('Invalid login input.', 422);
    }
    $users = SQLManager::findWhere(['id', 'password_hash'], 'users', ['email' => $email]);
    $user = $users[0] ?? null;
    if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
        return Responses::html('Invalid email or password.', 401);
    }
    SessionBootstrap::start();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    return Responses::redirect('/members', 303);
};
```

The generic credential error does not identify whether an email exists. For production, also address response-timing differences and throttling. Once several handlers need this behavior, move the lookup into a repository and session operations into an app-owned auth service, as the [forum authentication chapter](/guided-projects/forum/authentication) demonstrates.

### Protect A Directory

Create `src/Views/members/_middleware.php` with this complete file:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Bootstrap\SessionBootstrap;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

return [new class implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        SessionBootstrap::start();
        if (!is_int($_SESSION['user_id'] ?? null)) {
            return Responses::redirect('/login', 303);
        }
        return $handler->handle($request);
    }
}];
```

This is a login-state gate for `/members` and its children, not an admin-permission check. For account deletion, suspension, and permission changes to take effect immediately, reload the user from your repository before allowing protected operations. The [forum permissions chapter](/guided-projects/forum/permissions) shows how to centralize ability checks. Keep login outside this protected directory to avoid a redirect loop.

Create `src/Views/members/index.get.php` as a complete direct page:

```html
<h1>Members area</h1>
<form method="POST" action="/logout">
    <?= \CorianderCore\Core\Security\Csrf::input() ?>
    <button type="submit">Log out</button>
</form>
```

### Log Out With POST

Create `src/Views/logout.post.php` with this complete handler:

```php
<?php
use CorianderCore\Core\Bootstrap\SessionBootstrap;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    SessionBootstrap::start();
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
    return Responses::redirect('/login', 303);
};
```

This removes the example's authentication key and rotates the session ID while preserving unrelated session data. If your app stores sensitive state elsewhere in the session, explicitly clear that state too or apply full session invalidation. Removing a key alone is not a universal logout policy.

### Verify The Login Lifecycle

As a guest, `/members` redirects to `/login`. Submit valid seeded credentials through the form: the POST redirects to `/members`, and a subsequent GET remains logged in. Wrong credentials return 401; malformed field types return 422. A missing CSRF token is rejected by middleware before the handler. Use the logout form, then revisit `/members`: it redirects to `/login` again. Reloading the redirected GET must not ask you to resubmit a form.

See [Security](/documentation/security) for cookie settings, CSRF behavior, stateless API exceptions, and deployment safeguards.

## Flash And Post/Redirect/Get

```php
SessionBootstrap::start();
$_SESSION['flash'] = ['message' => 'Saved.'];
return \CorianderCore\Core\Http\Responses::redirect('/articles', 303);
```

The subsequent GET starts the session, reads the flash, and removes it so it appears once. Finish all session writes before calling `session_write_close()`; later writes would not be persisted.

Close an active session early only when a long-running operation no longer needs to change it. This releases the lock so another request from the same browser is not unnecessarily blocked.

## CSRF And Public Pages

`Csrf::token()`, `Csrf::input()`, and validation start the session when required. Rendering a CSRF-protected form therefore needs a session; a plain reference page need not.

Cookie-authenticated JSON writes still need CSRF tokens. See [Security](/documentation/security). Stateless APIs should not read browser authentication sessions merely because their URL starts with `/api`.
