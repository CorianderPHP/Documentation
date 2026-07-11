# Protected Demo Writes

The local guided project writes to SQLite. The official documentation site is different: it is public, so visitor-written text must not be stored.

## Goal

Keep the same forms, routes, validation, and permission checks while preventing public-site persistence when demo mode is enabled.

## File Created

```structure
src/Modules/ForumDemo/Writes/PublicDemoWriteGuard.php
```

## Step: Add A Demo Mode Flag

Use an environment flag for the hosted documentation site:

```env
FORUM_DEMO_READ_ONLY=true
```

Local learners should leave this disabled so topic and reply creation writes to SQLite.

## Step: Create The Guard

```php
<?php
declare(strict_types=1);

namespace Modules\ForumDemo\Writes;

use Modules\ForumDemo\Permissions\DemoPermissionService;

final class PublicDemoWriteGuard
{
    public function __construct(private readonly DemoPermissionService $permissions = new DemoPermissionService())
    {
    }
}
```

This guard is not the normal persistence layer. It is a protection layer for the public documentation deployment.

## Step: Check Permissions Before Demo Success

```php
public function protect(?array $user, string $ability, string $action, array $payload = []): ?array
{
    if (!$this->isReadOnlyDemo()) {
        return null;
    }

    if (!$this->permissions->can($user, $ability)) {
        return [
            'ok' => false,
            'demo' => true,
            'status' => 403,
            'message' => 'Permission denied for this demo action.',
            'action' => $action,
        ];
    }

    if (!$this->hasEnoughInput($payload)) {
        return [
            'ok' => false,
            'demo' => true,
            'status' => 422,
            'message' => 'Add content before submitting the demo form.',
            'action' => $action,
        ];
    }

    return [
        'ok' => true,
        'demo' => true,
        'status' => 200,
        'message' => 'Demo mode: your ' . $action . ' passed validation but was not saved.',
        'action' => $action,
    ];
}
```

Returning `null` means the real write service should continue and persist the data.

## Step: Implement The Helpers

```php
private function isReadOnlyDemo(): bool
{
    return filter_var($_ENV['FORUM_DEMO_READ_ONLY'] ?? false, FILTER_VALIDATE_BOOL);
}

private function hasEnoughInput(array $payload): bool
{
    foreach ($payload as $value) {
        if (is_string($value) && trim($value) !== '') {
            return true;
        }
    }

    return false;
}
```

## Step: Use It Before Real Writes

```php
public function storeTopic(ServerRequestInterface $request): Response
{
    $payload = (array) $request->getParsedBody();
    $user = $this->auth->currentUser();

    $demoResult = $this->demoWriteGuard->protect($user, 'topic.create', 'create topic', $payload);
    $result = $demoResult ?? $this->writeService->createTopic($user, $payload);

    return $this->flashAndRedirect($result, '/forum-demo/topics');
}
```

The controller remains explicit: public demo protection is checked first, then real persistence handles normal local behavior. The response still redirects to a GET page so refresh and back navigation do not resubmit the form.

## Checkpoint

With `FORUM_DEMO_READ_ONLY=true`, submit the topic form at [/forum-demo/topics](/forum-demo/topics). You should get a clear demo success message and no database row should be created.

With the flag disabled locally, the same form should create a SQLite row.

## Common Mistakes

- Using protected demo writes as the main project architecture.
- Returning demo success without checking permissions.
- Letting the public documentation site store visitor-written content.

## Next

Continue with [API Endpoints](/guided-projects/forum/api).
