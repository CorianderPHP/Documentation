# Forum Controllers

Controllers should coordinate the request. They should not own forum data, role rules, or write behavior.

## Goal

Create a thin web controller that renders forum pages and delegates logic to modules.

## File Created

```structure
src/Controllers/ForumDemoController.php
```

## Step: Add Dependencies

The demo creates dependencies directly to stay beginner-friendly.

```php
namespace Controllers;

use CorianderCore\Core\Router\ViewRenderer;
use Modules\ForumDemo\Auth\DemoAuth;
use Modules\ForumDemo\Data\ForumRepository;
use Modules\ForumDemo\Data\UserRepository;
use Modules\ForumDemo\Permissions\DemoPermissionService;
use Modules\ForumDemo\Writes\ForumWriteService;
use Modules\ForumDemo\Writes\PublicDemoWriteGuard;

final class ForumDemoController
{
    private ViewRenderer $view;
    private DemoAuth $auth;
    private ForumRepository $forum;
    private UserRepository $users;
    private DemoPermissionService $permissions;
    private ForumWriteService $writeService;
    private PublicDemoWriteGuard $demoWriteGuard;
}
```

In a larger app, a container or factory can build these dependencies. For a documentation project, direct construction is easier to follow.

## Step: Build The Constructor

```php
public function __construct()
{
    $this->view = new ViewRenderer();
    $this->auth = new DemoAuth();
    $this->forum = new ForumRepository();
    $this->users = new UserRepository();
    $this->permissions = new DemoPermissionService();
    $this->writeService = new ForumWriteService($this->permissions);
    $this->demoWriteGuard = new PublicDemoWriteGuard($this->permissions);
}
```

The controller now depends on small modules instead of doing everything itself.

## Step: Render The Landing Page

```php
public function index(): void
{
    $this->render('forum-demo', [
        'topics' => $this->forum->topics(),
    ]);
}
```

The controller asks the repository for data and passes it to the view.

## Step: Render Topic Pages

```php
public function topics(): void
{
    $this->render('forum-demo/topics', [
        'categories' => $this->forum->categories(),
        'topics' => $this->forum->topics(),
        'flash' => $this->consumeFlash(),
    ]);
}

public function showTopic(string $id): ?Response
{
    $topic = $this->forum->topic((int) $id);
    if ($topic === null) {
        return new Response(404, [], 'Topic not found.');
    }

    $this->render('forum-demo/topic', [
        'topic' => $topic,
        'replies' => $this->forum->repliesForTopic($topic['id']),
        'flash' => $this->consumeFlash(),
    ]);

    return null;
}
```

Return a response only when the controller needs a status code or redirect. Write feedback comes from a one-time session flash so the browser can safely refresh GET pages.

The controller prepares complete view data. The template should not call repositories, inspect session state, or ask the database for replies. That keeps rendering predictable and makes it easier to test request flow later.

## Step: Handle Write Requests

Write routes should check the public-demo guard first. When read-only demo mode is disabled, the same request continues into `ForumWriteService` and writes to SQLite.

```php
public function storeTopic(ServerRequestInterface $request): Response
{
    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();

    $demoResult = $this->demoWriteGuard->protect($user, 'topic.create', 'create topic', $payload);
    $result = $demoResult ?? $this->writeService->createTopic($user, $payload);

    return $this->flashAndRedirect($result, '/forum-demo/topics');
}

public function storeReply(ServerRequestInterface $request, string $topicId): Response
{
    $topic = $this->forum->topic((int) $topicId);
    if ($topic === null) {
        return new Response(404, [], 'Topic not found.');
    }

    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();

    $demoResult = $this->demoWriteGuard->protect($user, 'reply.create', 'create reply', $payload);
    $result = $demoResult ?? $this->writeService->createReply($user, (int) $topicId, $payload);

    return $this->flashAndRedirect($result, '/forum-demo/topics/' . (int) $topic['id']);
}

public function updateUserRole(ServerRequestInterface $request): Response
{
    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();

    $demoResult = $this->demoWriteGuard->protect($user, 'user.manage', 'update user role', $payload);
    $result = $demoResult ?? $this->writeService->updateUserRole($user, $payload);

    return $this->flashAndRedirect($result, '/forum-demo/admin/users');
}
```

The controller still does not contain SQL. It chooses the workflow and delegates persistence to the write service.

Use Post/Redirect/Get for every web form. The POST route validates and writes, stores a flash message, then redirects to a GET page. This prevents browser refresh/back navigation from showing a "confirm form resubmission" warning.

The controller is allowed to choose the next URL because navigation is request flow. It should not decide whether a member can create a topic or which SQL statement should run. Those decisions belong to the permission service and write service.

## Step: Keep Moderation In Context

Moderation forms can appear in more than one place. The topic page has buttons beside the content being reviewed, while the admin page has a moderation queue. Both should use the same POST routes, but they should not always return to the same screen.

```php
public function moderateTopic(ServerRequestInterface $request): Response
{
    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();

    $demoResult = $this->demoWriteGuard->protect($user, 'topic.lock', 'moderate topic', $payload);
    $result = $demoResult ?? $this->writeService->moderateTopic($user, $payload);

    if (($payload['return_to'] ?? '') === 'topic') {
        return $this->flashAndRedirect($result, '/forum-demo/topics/' . (int) ($payload['topic_id'] ?? 0));
    }

    return $this->flashAndRedirect($result, '/forum-demo/admin');
}
```

This keeps the interface predictable. If an admin clicks "Hide reply" while reading a topic, the flash message appears on that topic instead of moving the admin to the moderation dashboard.

Add small flash helpers to keep this DRY.

```php
private function flashAndRedirect(array $flash, string $location): Response
{
    $_SESSION['forum_demo_flash'] = $flash;
    return new Response(302, ['Location' => $location], '');
}

private function consumeFlash(): ?array
{
    $flash = $_SESSION['forum_demo_flash'] ?? null;
    unset($_SESSION['forum_demo_flash']);

    return is_array($flash) ? $flash : null;
}
```

The flash is intentionally one-time. If the user reloads the topic page again, the message disappears because the write already happened. That is less confusing than keeping success messages forever in query strings.

## Step: Keep Controller Rules Small

A good controller action in this project does five things at most:

- read route attributes or parsed form data
- load the page resource needed for a 404 check
- call the demo guard or write service
- choose the redirect target
- render a view for GET requests

If an action starts building SQL, checking raw roles, or formatting forum rows, move that work into a module.

## Step: Centralize Shared View Data

Every forum view needs the current user, permissions, and demo account labels. Add one private render helper.

```php
private function render(string $view, array $data): void
{
    $user = $this->auth->currentUser();

    $this->view->render($view, $data + [
        'currentUser' => $user,
        'permissions' => $this->permissions->matrix($user),
        'demoAccounts' => $this->demoAccounts(),
    ]);
}
```

This keeps the controller DRY and gives every template the same permission shape.

## Checkpoint

Open [/forum-demo/topics](/forum-demo/topics). The page should render topics from `ForumRepository` and receive a `permissions` array even when the visitor is a guest.

## Common Mistakes

- Querying arrays or SQL directly inside templates.
- Copying current-user setup into every action.
- Returning strings from some actions and rendering views from others without a clear reason.

## Next

Continue with [Views](/guided-projects/forum/views).
