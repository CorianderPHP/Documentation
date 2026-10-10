# Sessions And Flash Messages

CorianderPHP 0.3.0 configures session cookies during bootstrap but does not start a session for every page. A public GET page can remain session-free.

## Start Before Reading Or Writing

```php
use CorianderCore\Core\Bootstrap\SessionBootstrap;

SessionBootstrap::start();
$userId = $_SESSION['user_id'] ?? null;
```

Call this before authentication, permission checks based on session state, or flashes. Reading `$_SESSION` without starting/resuming the session can make a logged-in user appear logged out.

The helper can be called again by code that also needs a session. Configure cookies through the starter before starting the session; do not duplicate raw `session_start()` calls throughout the app.

## Login And Logout

After verifying a password, rotate the session id before storing the authenticated user id:

```php
SessionBootstrap::start();
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
```

The user array comes from your authenticated repository lookup; never trust a submitted id or role. On logout, remove your authentication entry. Apply your app's session invalidation policy when sensitive state is also stored.

The [forum authentication chapter](/guided-projects/forum/authentication) demonstrates the shared app-owned auth service.

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
