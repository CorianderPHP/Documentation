# Forum Permissions

Permissions should live in one place. Do not scatter role checks across request handlers, middleware, and views.

## Goal

Create an ability-based permission service that answers questions like "can this user create a topic?"

## File Created

```structure
src/Modules/ForumDemo/Permissions/DemoPermissionService.php
```

## Step: Create Ability Names

Use ability strings instead of asking for roles everywhere.

```txt
topic.create
reply.create
topic.lock
reply.moderate
category.manage
user.manage
admin.view
```

This keeps the app flexible. If moderators are added later, you update the permission service, not every handler.

## Step: Implement `can`

```php
<?php
declare(strict_types=1);

namespace App\Modules\ForumDemo\Permissions;

final class DemoPermissionService
{
    public function can(?array $user, string $ability): bool
    {
        $role = $user['role'] ?? 'guest';

        return match ($ability) {
            'topic.create', 'reply.create' => in_array($role, ['member', 'moderator', 'admin'], true),
            'topic.lock', 'reply.moderate' => in_array($role, ['moderator', 'admin'], true),
            'category.manage', 'user.manage', 'admin.view' => $role === 'admin',
            default => false,
        };
    }
}
```

The handler, middleware, API, and views can all call the same rule.

## Step: Add A View Matrix

Views often need several flags. Build them once.

```php
public function matrix(?array $user): array
{
    return [
        'topic.create' => $this->can($user, 'topic.create'),
        'reply.create' => $this->can($user, 'reply.create'),
        'topic.lock' => $this->can($user, 'topic.lock'),
        'reply.moderate' => $this->can($user, 'reply.moderate'),
        'user.manage' => $this->can($user, 'user.manage'),
        'admin.view' => $this->can($user, 'admin.view'),
    ];
}
```

The handler passes this matrix into every forum view.

## Step: Use Permissions In Write Services

Inside a write-service method, check an ability before saving and return a result array. The HTTP handler converts that result into a view, redirect, or JSON response; the array itself is not a valid route response.

```php
if (!$this->permissions->can($user, 'topic.create')) {
    return [
        'ok' => false,
        'status' => 403,
        'message' => 'Permission denied.',
    ];
}
```

The write service does not inspect roles directly. It only asks for an ability.

## Checkpoint

Log in as the member account and open [/forum-demo/admin](/forum-demo/admin). Then log in as admin and open the same URL. The member should be blocked; the admin should enter.

## Common Mistakes

- Checking `$user['role'] === 'admin'` directly in templates.
- Creating different permission rules for web and API code.
- Naming abilities after UI labels instead of actions.

## Next

Continue with [Admin Middleware](/guided-projects/forum/admin-area).
