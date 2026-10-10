# Forum API Endpoints

The forum API shares the browser login session. It therefore uses the same permissions and **root CSRF middleware** as web forms. Do not exempt `api/forum-demo`.

## Create The Action Class

Create `src/Actions/ForumApiActions.php`. The modules are the ones built in previous chapters:

```php
<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\ForumDemo\Auth\DemoAuth;
use App\Modules\ForumDemo\Permissions\DemoPermissionService;
use App\Modules\ForumDemo\Writes\ForumWriteService;
use App\Modules\ForumDemo\Writes\PublicDemoWriteGuard;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ForumApiActions
{
    private DemoAuth $auth;
    private ForumWriteService $writes;
    private PublicDemoWriteGuard $guard;

    public function __construct()
    {
        $permissions = new DemoPermissionService();
        $this->auth = new DemoAuth();
        $this->writes = new ForumWriteService($permissions);
        $this->guard = new PublicDemoWriteGuard($permissions);
    }

    public function post_topic(ServerRequestInterface $request): ResponseInterface
    {
        return $this->write($request, 'topic.create', 'create topic');
    }

    public function post_reply(ServerRequestInterface $request): ResponseInterface
    {
        return $this->write($request, 'reply.create', 'create reply');
    }

    public function post_moderate(ServerRequestInterface $request): ResponseInterface
    {
        return $this->write($request, 'reply.moderate', 'moderate reply');
    }

    private function write(ServerRequestInterface $request, string $ability, string $action): ResponseInterface
    {
        $payload = (array) $request->getParsedBody();
        $user = $this->auth->currentUser();
        $demo = $this->guard->protect($user, $ability, $action, $payload);
        $result = $demo ?? $this->writes->run($user, $ability, $payload);
        return Responses::json($result, $result['status']);
    }
}
```

`DemoAuth::currentUser()` starts/resumes the session. The request factory parses JSON before routing; malformed JSON fails with 400 rather than silently becoming an empty array.

The local write service persists authorized input. The hosted guard returns `demo: true` without saving it.

## Create Three Method Files

Create `src/Routes/api/forum-demo/topic.post.php`:

```php
<?php
use App\Actions\ForumApiActions;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    (new ForumApiActions())->post_topic($request);
```

Create `reply.post.php` and `moderate.post.php` in the same directory, using the same imports and callable but delegating to `post_reply()` and `post_moderate()` respectively. No route include or API controller discovery is needed.

Run `php coriander routes:list` to check all three POST endpoints.

## Send The Token In The Body

On a logged-in forum page, read the existing hidden CSRF input:

```typescript
const token = document.querySelector<HTMLInputElement>('input[name="csrf_token"]')?.value;
if (!token) throw new Error('Missing CSRF token');

const response = await fetch('/api/forum-demo/topic', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        csrf_token: token,
        category_id: 1,
        title: 'My first topic',
        body: 'Created through the API.',
    }),
});
const result = await response.json();
```

0.3.0's built-in middleware reads `csrf_token` in the parsed body, **not X-CSRF-Token**. The session cookie and token must belong to the same session.

Include `topic_id` for reply creation and `reply_id` for reply moderation. The action maps an endpoint to a fixed ability; never accept an arbitrary privileged ability from visitor input.

## Error Format And Checks

Business errors use the result's HTTP status: 403 permission denial, 422 validation, 201 local creation. Accepted hosted demo actions return 200 with `demo: true`.

The framework's default CSRF rejection is plain text with status 403. If your client always expects JSON, wrap non-JSON error responses in app-owned root middleware. This website does that; do not assume the starter supplies its custom error envelope.

```workflow
Request factory|Parses and bounds the JSON request.
Root CSRF|Rejects a missing/incorrect token before the action.
Auth and permissions|Resolve the session user and check the ability.
Write layer|Persists locally or returns a protected hosted demo result.
JSON response|Returns a result with the correct HTTP status.
```

Test no token, valid token without login, member topic creation, member moderation denial, and admin moderation success. Confirm hosted demo content never appears in the database/list afterward.

Continue with [MySQL And Production](/guided-projects/forum/real-database).
