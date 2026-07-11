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

## Step: Add Convention-Based Methods

CorianderPHP maps API paths to methods by convention.

```php
public function post_topic(): array
{
    return $this->write('topic.create', 'create topic');
}

public function post_reply(): array
{
    return $this->write('reply.create', 'create reply');
}

public function post_moderate(): array
{
    return $this->write('reply.moderate', 'moderate reply');
}
```

Returning an array lets the framework encode JSON for you.

## Step: Read JSON Payloads

```php
private function write(string $ability, string $action): array
{
    $payload = json_decode((string) file_get_contents('php://input'), true);
    $payload = is_array($payload) ? $payload : $_POST;

    $user = $this->auth->currentUser();
    $demoResult = $this->demoWriteGuard->protect($user, $ability, $action, $payload);

    return $demoResult ?? $this->writeService->run($user, $ability, $payload);
}
```

The API and web controllers both check the same public-demo protection before calling the real write service.

For `reply.create`, include `topic_id` in the JSON body so `ForumWriteService::run()` can call `createReply()` with the correct topic.

## API Routes

```txt
POST /api/forum-demo/topic
POST /api/forum-demo/reply
POST /api/forum-demo/moderate
```

## CSRF Difference

The framework CSRF middleware protects mutating web requests, but skips `/api/*`. That keeps APIs suitable for stateless clients.

If an API endpoint is only used by same-site pages, you can validate tokens manually, but make that decision explicit.

## Checkpoint

Call `/api/forum-demo/topic` with a JSON body as a guest. The response should be JSON and should reject the action because guests cannot create topics.

## Common Mistakes

- Returning HTML from API controllers.
- Implementing different permission rules for API and web requests.
- Assuming CSRF behavior is the same for `/api/*` and web routes.

## Next

Continue with [MySQL And Production](/guided-projects/forum/real-database).
