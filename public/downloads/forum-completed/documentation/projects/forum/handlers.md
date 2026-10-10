# Forum Request Handlers

Routes choose the endpoint. `ForumActions` coordinates repositories, permissions, view responses, and form redirects. SQL and authorization rules stay in app modules.

## Dependencies And Construction

Create `src/Actions/ForumActions.php`:

```php
<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\ForumDemo\Auth\DemoAuth;
use App\Modules\ForumDemo\Data\ForumRepository;
use App\Modules\ForumDemo\Data\UserRepository;
use App\Modules\ForumDemo\Permissions\DemoPermissionService;
use App\Modules\ForumDemo\Writes\ForumWriteService;
use App\Modules\ForumDemo\Writes\PublicDemoWriteGuard;
use CorianderCore\Core\Bootstrap\SessionBootstrap;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ForumActions
{
    private DemoAuth $auth;
    private ForumRepository $forum;
    private UserRepository $users;
    private DemoPermissionService $permissions;
    private ForumWriteService $writeService;
    private PublicDemoWriteGuard $demoWriteGuard;

    public function __construct()
    {
        SessionBootstrap::start();
        $this->auth = new DemoAuth();
        $this->forum = new ForumRepository();
        $this->users = new UserRepository();
        $this->permissions = new DemoPermissionService();
        $this->writeService = new ForumWriteService($this->permissions);
        $this->demoWriteGuard = new PublicDemoWriteGuard($this->permissions);
    }
}
```

Add the methods below **inside this class**, before its final brace. The auth, permission, and write classes are completed in later chapters; the full feature is not runnable until those dependencies exist.

These are ordinary PHP classes under the starter's App namespace. There is no Container or controller discovery to configure.

## Shared View Context

```php
private function render(string $name, array $data): ResponseInterface
{
    $user = $this->auth->currentUser();
    return Responses::view($name, $data + [
        'title' => 'Forum',
        'currentUser' => $user,
        'permissions' => $this->permissions->matrix($user),
    ]);
}
```

This private `render()` is **our helper**, not the removed framework method. It returns a response and supplies shared variables to every template. `Responses::view()` renders `src/Views` and recursively escapes string-array data.

## Read Pages And Missing Topics

```php
public function index(ServerRequestInterface $request): ResponseInterface
{
    return $this->render('forum-demo', ['topics' => $this->forum->topics()]);
}

public function topics(): ResponseInterface
{
    return $this->render('forum-demo/topics', [
        'categories' => $this->forum->categories(),
        'topics' => $this->forum->topics(),
        'flash' => $this->consumeFlash(),
    ]);
}

public function showTopic(string $id): ResponseInterface
{
    $topic = $this->forum->topic((int) $id);
    if ($topic === null) {
        return Responses::html('Topic not found.', 404);
    }
    return $this->render('forum-demo/topic', [
        'title' => $topic['title'],
        'topic' => $topic,
        'replies' => $this->forum->repliesForTopic($topic['id']),
        'flash' => $this->consumeFlash(),
    ]);
}
```

```workflow
Method file|Selects ForumActions::showTopic() after id middleware permits the request.
Repository|ForumRepository loads the record and replies.
View response|Responses::view('forum-demo/topic', ...) passes prepared variables.
Template|src/Views/forum-demo/topic.php renders the original post before replies.
```

Success must return a response too, not `null`. A numeric but missing topic returns 404.

## Write, Flash, Redirect

```php
public function storeTopic(ServerRequestInterface $request): ResponseInterface
{
    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();
    $demo = $this->demoWriteGuard->protect($user, 'topic.create', 'create topic', $payload);
    $result = $demo ?? $this->writeService->createTopic($user, $payload);
    return $this->flashAndRedirect($result, '/forum-demo/topics');
}

public function storeReply(ServerRequestInterface $request, string $id): ResponseInterface
{
    if ($this->forum->topic((int) $id) === null) {
        return Responses::html('Topic not found.', 404);
    }
    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();
    $demo = $this->demoWriteGuard->protect($user, 'reply.create', 'create reply', $payload);
    $result = $demo ?? $this->writeService->createReply($user, (int) $id, $payload);
    return $this->flashAndRedirect($result, '/forum-demo/topics/' . (int) $id);
}

private function flashAndRedirect(array $flash, string $location): ResponseInterface
{
    $_SESSION['forum_demo_flash'] = $flash;
    return Responses::redirect($location, 303);
}

private function consumeFlash(): ?array
{
    $flash = $_SESSION['forum_demo_flash'] ?? null;
    unset($_SESSION['forum_demo_flash']);
    return is_array($flash) ? $flash : null;
}
```

The constructor starts the session before these reads/writes. Root middleware handles CSRF; the write service enforces abilities and validates content. A local build calls SQL writes; the optional public guard returns fake success without persistence.

303 implements Post/Redirect/Get. Refresh/back navigation revisits the GET page, not the submitted form.

## Login And Logout

After creating `DemoAuth`, add:

```php
public function login(): ResponseInterface
{
    return $this->render('forum-demo/login', ['error' => null]);
}

public function authenticate(ServerRequestInterface $request): ResponseInterface
{
    $payload = (array) $request->getParsedBody();
    $email = $payload['email'] ?? '';
    $password = $payload['password'] ?? '';
    if (is_string($email) && is_string($password) && $this->auth->login($email, $password)) {
        return Responses::redirect('/forum-demo', 303);
    }
    return $this->render('forum-demo/login', ['error' => 'Invalid credentials.']);
}

public function logout(): ResponseInterface
{
    $this->auth->logout();
    return Responses::redirect('/forum-demo', 303);
}
```

Demo-only quick-role buttons are optional. Never offer unauthenticated "use admin" shortcuts in a production forum.

## Admin Reads And Writes

```php
public function admin(): ResponseInterface
{
    return $this->render('forum-demo/admin', [
        'topics' => $this->forum->topics(),
        'flash' => $this->consumeFlash(),
    ]);
}

public function adminUsers(): ResponseInterface
{
    return $this->render('forum-demo/admin-users', [
        'users' => $this->users->all(),
        'flash' => $this->consumeFlash(),
    ]);
}

public function updateUserRole(ServerRequestInterface $request): ResponseInterface
{
    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();
    $demo = $this->demoWriteGuard->protect($user, 'user.manage', 'update user role', $payload);
    $result = $demo ?? $this->writeService->updateUserRole($user, $payload);
    return $this->flashAndRedirect($result, '/forum-demo/admin/users');
}

public function moderateTopic(ServerRequestInterface $request): ResponseInterface
{
    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();
    $demo = $this->demoWriteGuard->protect($user, 'topic.lock', 'moderate topic', $payload);
    $result = $demo ?? $this->writeService->moderateTopic($user, $payload);
    $location = ($payload['return_to'] ?? '') === 'topic'
        ? '/forum-demo/topics/' . (int) ($payload['topic_id'] ?? 0)
        : '/forum-demo/admin';
    return $this->flashAndRedirect($result, $location);
}

public function moderateReply(ServerRequestInterface $request): ResponseInterface
{
    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();
    $demo = $this->demoWriteGuard->protect($user, 'reply.moderate', 'moderate reply', $payload);
    $result = $demo ?? $this->writeService->moderateReply($user, $payload);
    $location = ($payload['return_to'] ?? '') === 'topic'
        ? '/forum-demo/topics/' . (int) ($payload['topic_id'] ?? 0)
        : '/forum-demo/admin';
    return $this->flashAndRedirect($result, $location);
}
```

Only choose from known internal destinations; never redirect directly to an arbitrary `return_to` URL. Admin-directory middleware gates the area; the write service still checks the individual ability.

## Checkpoint

Once later dependencies/templates are implemented, visit topics as a guest, submit as a member, and moderate as admin. Verify flashes appear once and admin actions return to the page where they were initiated.

The completed download is the site's **read-only demo reference**, with `DemoForumRepository`/`DemoWriteGuard`. This chapter's `ForumRepository`/`ForumWriteService` are the real SQLite implementation you build locally.

Continue with [Views](/guided-projects/forum/views).
