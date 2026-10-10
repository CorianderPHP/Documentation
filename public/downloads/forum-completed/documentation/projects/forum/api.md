# API Endpoints

API controllers live under `src/ApiControllers`. The forum API exposes write endpoints that use the same permissions, write service, and public-demo protection as the web forms.

## Goal

Create JSON endpoints for topic creation, reply creation, and moderation attempts.

## File Created

```structure
src/ApiControllers/ForumDemoController.php
```

## Step: Create The API Controller

```php
<?php
declare(strict_types=1);

namespace ApiControllers;

use Modules\ForumDemo\Auth\DemoAuth;
use Modules\ForumDemo\Permissions\DemoPermissionService;
use Modules\ForumDemo\Writes\ForumWriteService;
use Modules\ForumDemo\Writes\PublicDemoWriteGuard;
use CorianderCore\Core\Bootstrap\SessionBootstrap;
use CorianderCore\Core\Security\Csrf;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class ForumDemoController
{
    private DemoAuth $auth;
    private ForumWriteService $writeService;
    private PublicDemoWriteGuard $demoWriteGuard;
}
```

API controllers are separate from web controllers so JSON behavior does not leak into templates.

## Step: Build Dependencies

```php
public function __construct()
{
    $permissions = new DemoPermissionService();
    $this->auth = new DemoAuth();
    $this->writeService = new ForumWriteService($permissions);
    $this->demoWriteGuard = new PublicDemoWriteGuard($permissions);
}
```

This mirrors the web controller permissions and write behavior.

## Step: Add Request Actions

Each action receives a PSR-7 request and returns a JSON response. We register its URL in the next step; the method name does not expose it automatically.

```php
public function post_topic(ServerRequestInterface $request): Response
{
    return $this->write($request, 'topic.create', 'create topic');
}

public function post_reply(ServerRequestInterface $request): Response
{
    return $this->write($request, 'reply.create', 'create reply');
}

public function post_moderate(ServerRequestInterface $request): Response
{
    return $this->write($request, 'reply.moderate', 'moderate reply');
}
```

Explicit routes preserve PSR-7 responses. Returning a plain array does not produce a JSON response; the helper below encodes it and sets its HTTP status.

## Step: Read JSON Payloads

```php
private function write(ServerRequestInterface $request, string $ability, string $action): Response
{
    // This API shares the web login cookie; it is not stateless.
    SessionBootstrap::start();
    if (!Csrf::validate($request->getHeaderLine('X-CSRF-Token'))) {
        return $this->json(['ok' => false, 'message' => 'Invalid CSRF token.'], 403);
    }

    $body = (string) $request->getBody();
    try {
        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
        return $this->json(['ok' => false, 'message' => 'Invalid JSON body.'], 400);
    }
    if (!is_array($payload) || !str_starts_with(ltrim($body), '{')) {
        return $this->json(['ok' => false, 'message' => 'Send a JSON object.'], 400);
    }

    $user = $this->auth->currentUser();
    $demoResult = $this->demoWriteGuard->protect($user, $ability, $action, $payload);

    $result = $demoResult ?? $this->writeService->run($user, $ability, $payload);
    return $this->json($result, $result['status']);
}

private function json(array $payload, int $status): Response
{
    return new Response(
        $status,
        ['Content-Type' => 'application/json; charset=utf-8'],
        json_encode($payload, JSON_THROW_ON_ERROR)
    );
}
```

The API and web controllers both check the same public-demo protection before calling the real write service.

For `reply.create`, include `topic_id` in the JSON body so `ForumWriteService::run()` can call `createReply()` with the correct topic.

## Step: Register The API Routes

Add these definitions inside the route closure in `src/Routes/forum-demo.php`. Import the API controller under an alias so it does not conflict with the web controller:

```php
use ApiControllers\ForumDemoController as ForumDemoApiController;
```

```php
$router->post('api/forum-demo/topic', static fn (ServerRequestInterface $request) =>
    (new ForumDemoApiController())->post_topic($request)
);
$router->post('api/forum-demo/reply', static fn (ServerRequestInterface $request) =>
    (new ForumDemoApiController())->post_reply($request)
);
$router->post('api/forum-demo/moderate', static fn (ServerRequestInterface $request) =>
    (new ForumDemoApiController())->post_moderate($request)
);
```

The route file is already included by `public/routes.php` from the routing chapter. These endpoints accept POST only. Do not enable automatic routing to make them reachable.

## CSRF Difference

The framework CSRF middleware protects mutating web requests, but skips `/api/*`. That keeps APIs suitable for stateless clients.

The forum API uses the user's session cookie, so it must validate a CSRF token. `SessionBootstrap::start()` resumes that session before permission checks, and `Csrf::validate()` checks the `X-CSRF-Token` header. Keep both checks even when public demo writes are disabled: a local project stores real content.

## Step: Call The API From A Forum Page

Include `Csrf::input()` in the page's form, then read the token when sending JSON:

```typescript
const token = document.querySelector<HTMLInputElement>('input[name="csrf_token"]')?.value;
if (!token) throw new Error('Missing CSRF token');

const response = await fetch('/api/forum-demo/topic', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token,
    },
    body: JSON.stringify({
        category_id: 1,
        title: 'My first topic',
        body: 'Created through the API.',
    }),
});
const result = await response.json();
```

`response.ok` represents the HTTP result; `result.message` is feedback for your UI. An authorized local creation returns 201, invalid input returns 422, and rejected permission or CSRF checks return 403. The hosted demo returns 200 with `demo: true` for an accepted action without saving it.

## Checkpoint

Without a valid CSRF token, a POST must return JSON with status 403. With a valid token but no logged-in user, it must still return 403 for the permission check. Log in as a member and retry a valid topic payload: it should succeed. A member must not be able to use the moderation endpoint; an admin can.

## Common Mistakes

- Returning HTML from API controllers.
- Implementing different permission rules for API and web requests.
- Assuming CSRF behavior is the same for `/api/*` and web routes.
- Returning an array without a JSON response, or returning HTTP 200 when permission checks failed.

## Next

Continue with [MySQL And Production](/guided-projects/forum/real-database).
