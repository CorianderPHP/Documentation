# Admin Middleware

Protect every method under /forum-demo/admin with inherited directory middleware. Hiding a link is not authorization.

## Create The Gate

Create `src/Middleware/ForumDemoAdminMiddleware.php`:

```php
<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Modules\ForumDemo\Auth\DemoAuth;
use App\Modules\ForumDemo\Permissions\DemoPermissionService;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ForumDemoAdminMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $user = (new DemoAuth())->currentUser();
        if (!(new DemoPermissionService())->can($user, 'admin.view')) {
            return Responses::redirect('/forum-demo/login', 302);
        }
        return $handler->handle($request);
    }
}
```

DemoAuth starts the session before reading login state. The permission service owns the role rule; middleware only asks for the ability.

## Attach It Once

Create `src/Routes/forum-demo/admin/_middleware.php`:

```php
<?php
use App\Middleware\ForumDemoAdminMiddleware;

return [new ForumDemoAdminMiddleware()];
```

This protects admin/index.get.php, users.get.php, users.post.php, topics.post.php, and replies.post.php. Root CSRF, request limits, and security headers still apply. A child list cannot remove them.

Create `src/Routes/forum-demo/admin/index.get.php`:

```php
<?php
use App\Actions\ForumActions;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    (new ForumActions())->admin();
```

For the other files, use the same callable pattern with the methods in [Forum Routes](/guided-projects/forum/routes). Pass the request into writes.

## Individual Abilities Still Matter

Directory middleware gates the admin area. The write service separately checks `topic.lock`, `reply.moderate`, and `user.manage`, because the same service is also used from API handlers.

The [handler chapter](/guided-projects/forum/handlers) defines moderation/role actions and the shared flash helpers. Do not paste a second copy of those methods into the class.

Forms send a known context such as `return_to=topic` or `return_to=admin`. The action chooses a fixed GET destination and returns a 303 redirect after the result is stored in the session. Do not redirect directly to an arbitrary submitted URL.

## Checkpoint

- Guest or member GET /forum-demo/admin: redirect to login.
- Admin GET: render the moderation page.
- Member with a valid CSRF token POST to an admin endpoint: still denied.
- Admin without a token: CSRF denial before any write.
- Admin moderation from a topic page: redirect back to that topic with one flash.

The hosted demo validates but never saves visitor moderation. Your local write service persists it.

Continue with [Write Service](/guided-projects/forum/write-service).
