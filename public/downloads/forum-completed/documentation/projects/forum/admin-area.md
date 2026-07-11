# Admin Middleware

The admin area demonstrates route-group middleware and role-based access.

## Goal

Protect `/forum-demo/admin` and its child routes with one middleware declaration.

## File Created

```structure
src/Middleware/ForumDemoAdminMiddleware.php
```

## Step: Create The Middleware

```php
<?php
declare(strict_types=1);

namespace Middleware;

use Modules\ForumDemo\Auth\DemoAuth;
use Modules\ForumDemo\Permissions\DemoPermissionService;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ForumDemoAdminMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = (new DemoAuth())->currentUser();
        if (!(new DemoPermissionService())->can($user, 'admin.view')) {
            return new Response(302, ['Location' => '/forum-demo/login'], '');
        }

        return $handler->handle($request);
    }
}
```

The middleware asks for `admin.view`. It does not care whether the rule currently means admin-only or something more complex later.

## Step: Attach Middleware To The Route Group

```php
$router->group('forum-demo/admin', [new ForumDemoAdminMiddleware()], static function (Router $admin): void {
    $admin->get('', static fn () => (new ForumDemoController())->admin());
    $admin->get('users', static fn () => (new ForumDemoController())->adminUsers());
    $admin->post('users', static fn (ServerRequestInterface $request) =>
        (new ForumDemoController())->updateUserRole($request)
    );
});
```

All routes inside the group inherit the same protection.

## Step: Keep Admin Controllers Simple

```php
public function adminUsers(): void
{
    $this->render('forum-demo/admin-users', [
        'users' => $this->users->all(),
        'flash' => null,
    ]);
}
```

The controller action does not repeat the admin check because the route group already handled it.

## Step: Add Moderation Actions

Admin routes should cover more than user roles. Add topic and reply moderation actions so permissions are visible in the live demo.

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

Use the same shape for reply moderation with the `reply.moderate` ability. The important part is that admin actions are server-side routes, not just hidden buttons in a view.

The action route should not force one destination. A lock button shown on the topic page should keep the admin on that topic; the same lock action shown in the moderation queue should keep the admin on the queue. Pass `return_to=topic` or `return_to=admin` from the form and redirect to the matching GET page with the write result in a one-time flash message.

Add a small redirect helper and consume the flash in the GET actions. This follows Post/Redirect/Get and prevents the browser from asking users to resubmit moderation forms when they refresh or go back.

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

## Checkpoint

Open [/forum-demo/admin](/forum-demo/admin) as guest, member, and admin.

- Guest should go to login.
- Member should not enter the admin area.
- Admin should see admin content.

## Common Mistakes

- Protecting links but not routes.
- Repeating admin checks inside every admin action.
- Returning a public 200 page for forbidden admin requests.

## Next

Continue with [Write Service](/guided-projects/forum/write-service).
