# Forum Write Service

The read repositories load forum data. Write behavior belongs in a separate service so validation, permissions, and SQL updates do not spread across controllers.

## Goal

Create `ForumWriteService` with real SQLite writes for topics, replies, moderation, and user role changes.

## File Created

```structure
src/Modules/ForumDemo/Writes/ForumWriteService.php
```

## Step: Create The Service

```php
<?php
declare(strict_types=1);

namespace Modules\ForumDemo\Writes;

use CorianderCore\Core\Database\SQLManager;
use Modules\ForumDemo\Permissions\DemoPermissionService;

final class ForumWriteService
{
    public function __construct(private readonly DemoPermissionService $permissions = new DemoPermissionService())
    {
    }
}
```

The service depends on permissions, not on the controller. That keeps the same rules available to web forms and API endpoints.

The write service is the first place in the project where input, permissions, and persistence meet. Keep it narrow: it should not know about HTML templates, redirects, or sessions.

## Step: Add A Result Helper

Use one response shape for all writes:

```php
private function result(bool $ok, int $status, string $message, string $action): array
{
    return [
        'ok' => $ok,
        'demo' => false,
        'status' => $status,
        'message' => $message,
        'action' => $action,
    ];
}
```

Views and API controllers can display this without knowing which SQL statement ran.

## Step: Validate Permission And Input

```php
private function canWrite(?array $user, string $ability, string $action): ?array
{
    if (!$this->permissions->can($user, $ability)) {
        return $this->result(false, 403, 'Permission denied.', $action);
    }

    return null;
}

private function text(array $payload, string $key): string
{
    return trim((string) ($payload[$key] ?? ''));
}
```

The controller should not inspect roles or normalize form fields.

## Step: Create Topics

```php
public function createTopic(?array $user, array $payload): array
{
    $action = 'create topic';
    $denied = $this->canWrite($user, 'topic.create', $action);
    if ($denied !== null) {
        return $denied;
    }

    $title = $this->text($payload, 'title');
    $body = $this->text($payload, 'body');
    $categoryId = (int) ($payload['category_id'] ?? 0);

    if ($title === '' || $body === '' || $categoryId < 1 || $user === null) {
        return $this->result(false, 422, 'Add a category, title, and body before creating a topic.', $action);
    }

    SQLManager::sqlScript(
        'INSERT INTO topics (category_id, user_id, title, body)
         VALUES (:category_id, :user_id, :title, :body)',
        [
            'category_id' => $categoryId,
            'user_id' => (int) $user['id'],
            'title' => $title,
            'body' => $body,
        ]
    );

    return $this->result(true, 201, 'Topic created.', $action);
}
```

`sqlScript()` returns `true` for successful write statements. If the insert fails, the framework throws a database exception, which should be logged by your normal error handling.

For a larger forum, wrap topic creation in a transaction when you create related rows at the same time, such as tags, subscriptions, or audit events. A single insert is fine here because the topic row is the only required write.

## Step: Create Replies

```php
public function createReply(?array $user, int $topicId, array $payload): array
{
    $action = 'create reply';
    $denied = $this->canWrite($user, 'reply.create', $action);
    if ($denied !== null) {
        return $denied;
    }

    $body = $this->text($payload, 'body');
    if ($body === '' || $topicId < 1 || $user === null) {
        return $this->result(false, 422, 'Add a reply before submitting.', $action);
    }

    SQLManager::sqlScript(
        'INSERT INTO replies (topic_id, user_id, body)
         VALUES (:topic_id, :user_id, :body)',
        [
            'topic_id' => $topicId,
            'user_id' => (int) $user['id'],
            'body' => $body,
        ]
    );

    return $this->result(true, 201, 'Reply created.', $action);
}
```

The service does not redirect. It returns a result; the controller stores that result as a flash message and redirects to a GET page.

Before inserting a reply in a production version, also check whether the topic is locked. The demo keeps the example small, but a complete forum should reject replies for locked topics inside the write service, not only by hiding the form in the view.

## Step: Moderate Replies

```php
public function moderateReply(?array $user, array $payload): array
{
    $action = 'moderate reply';
    $denied = $this->canWrite($user, 'reply.moderate', $action);
    if ($denied !== null) {
        return $denied;
    }

    $replyId = (int) ($payload['reply_id'] ?? 0);
    if ($replyId < 1 || $user === null) {
        return $this->result(false, 422, 'Choose a reply to moderate.', $action);
    }

    SQLManager::sqlScript(
        'UPDATE replies SET is_deleted = 1 WHERE id = :id',
        ['id' => $replyId]
    );

    SQLManager::sqlScript(
        'INSERT INTO moderation_events (admin_user_id, target_type, target_id, action)
         VALUES (:admin_user_id, :target_type, :target_id, :action)',
        [
            'admin_user_id' => (int) $user['id'],
            'target_type' => 'reply',
            'target_id' => $replyId,
            'action' => 'delete',
        ]
    );

    return $this->result(true, 200, 'Reply moderated.', $action);
}
```

This records an audit event instead of silently deleting content.

In a real forum, prefer soft deletion for moderation. Keep the row, hide it from normal readers, and store who acted on it. That gives admins a history when a moderation decision needs review.

## Step: Moderate Topics

Topic moderation changes the topic status instead of creating discussion content.

```php
public function moderateTopic(?array $user, array $payload): array
{
    $action = 'moderate topic';
    $denied = $this->canWrite($user, 'topic.lock', $action);
    if ($denied !== null) {
        return $denied;
    }

    $topicId = (int) ($payload['topic_id'] ?? 0);
    $moderationAction = $this->text($payload, 'action');
    if ($topicId < 1 || !in_array($moderationAction, ['lock', 'unlock', 'hide'], true)) {
        return $this->result(false, 422, 'Choose a topic moderation action.', $action);
    }

    $locked = $moderationAction === 'lock' ? 1 : 0;
    SQLManager::sqlScript(
        'UPDATE topics SET locked = :locked WHERE id = :id',
        ['locked' => $locked, 'id' => $topicId]
    );

    return $this->result(true, 200, 'Topic moderation saved.', $action);
}
```

If your production schema has a separate `status` column, update that column instead of using only `locked`.

If you support both `hide` and `lock`, model them as separate state changes:

- `locked` prevents new replies.
- `is_deleted` or `status = hidden` removes the topic from public lists.
- `moderation_events` records who performed the action.

Do not infer "hidden" from "locked"; those are different moderation decisions.

## Step: Update User Roles

```php
public function updateUserRole(?array $user, array $payload): array
{
    $action = 'update user role';
    $denied = $this->canWrite($user, 'user.manage', $action);
    if ($denied !== null) {
        return $denied;
    }

    $targetUserId = (int) ($payload['user_id'] ?? 0);
    $role = $this->text($payload, 'role');
    if ($targetUserId < 1 || !in_array($role, ['admin', 'member'], true)) {
        return $this->result(false, 422, 'Choose a user and a valid role.', $action);
    }

    SQLManager::sqlScript(
        'UPDATE users SET role = :role WHERE id = :id',
        ['role' => $role, 'id' => $targetUserId]
    );

    return $this->result(true, 200, 'User role updated.', $action);
}
```

Keep role changes behind the same `user.manage` ability used by the admin middleware and views.

Avoid allowing an admin to demote the last admin account unless your app has a recovery path. That guard can live in this service after you add a real user repository query.

## Step: Add An API Dispatcher

The API chapter can route generic ability names into the specific methods:

```php
public function run(?array $user, string $ability, array $payload): array
{
    return match ($ability) {
        'topic.create' => $this->createTopic($user, $payload),
        'reply.create' => $this->createReply($user, (int) ($payload['topic_id'] ?? 0), $payload),
        'topic.lock' => $this->moderateTopic($user, $payload),
        'reply.moderate' => $this->moderateReply($user, $payload),
        'user.manage' => $this->updateUserRole($user, $payload),
        default => $this->result(false, 400, 'Unknown write action.', $ability),
    };
}
```

Web controllers can call the explicit method names. API controllers can use `run()` when they map routes to ability names.

## Checkpoint

With `FORUM_DEMO_READ_ONLY` disabled, log in as the member account and create a topic. The row should be inserted into SQLite, and the topic list should show it after reload.

## Common Mistakes

- Writing directly from controllers.
- Checking roles in multiple places instead of calling `DemoPermissionService`.
- Using string interpolation in SQL instead of bound `sqlScript()` parameters.

## Next

Continue with [Protected Demo Writes](/guided-projects/forum/fake-writes).
