# Forum Views

Views live under `public/public_views`. The demo uses server-rendered templates so the app works before JavaScript loads.

## Goal

Create forum templates that render prepared data and show controls based on permissions.

## Files Created

```structure
public/public_views/forum-demo/index.php
public/public_views/forum-demo/login/index.php
public/public_views/forum-demo/topics/index.php
public/public_views/forum-demo/topic/index.php
public/public_views/forum-demo/admin/index.php
public/public_views/forum-demo/admin-users/index.php
```

## Step: Render The Forum Landing Page

The landing page receives `$topics`, `$currentUser`, and `$permissions`. Keep it focused on the newest discussions and the current account state. Do not show non-clickable category blocks just because the database has categories.

```html
<h1>Forum with permissions</h1>

<?php foreach ($topics as $topic): ?>
    <a href="/forum-demo/topics/<?= (int) $topic['id'] ?>">
        <?= htmlspecialchars($topic['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </a>
<?php endforeach; ?>
```

The view does not decide where topics come from. It only renders what the controller prepared.

## Step: Render The Topic List

```html
<?php foreach ($topics as $topic): ?>
    <a href="/forum-demo/topics/<?= (int) $topic['id'] ?>">
        <?= htmlspecialchars($topic['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </a>
<?php endforeach; ?>
```

Always escape public strings. The local project stores user-created content in SQLite, so escaping is required from the beginning.

Use a forum-style row layout for the topic index: title and preview first, then updated time, reply count, and status. This is easier to scan than large repeated cards.

Categories still matter as metadata and form input. Show the category name beside each topic, and keep the category select in the create-topic form. Avoid a separate category sidebar unless categories are clickable filters.

## Step: Render The Original Post

The topic detail page should show the original post before any replies.

```html
<article>
    <h1><?= htmlspecialchars($topic['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars($topic['body'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
</article>
```

Replies should be displayed under the original post as a timeline. Do not make replies look like the main topic content.

## Step: Add Permission-Based UI

The topic creation form should only appear when the user can create topics.

```html
<?php if ($permissions['topic.create'] ?? false): ?>
    <form method="POST" action="/forum-demo/topics">
        <?= \CorianderCore\Core\Security\Csrf::input() ?>
        <input name="title" placeholder="Topic title">
        <textarea name="body" placeholder="What do you want to ask?"></textarea>
        <button>Create topic</button>
    </form>
<?php else: ?>
    <a href="/forum-demo/login">Log in to create a topic</a>
<?php endif; ?>
```

Hiding a form is UX, not security. The controller and write service still enforce permissions.

The same rule applies to admin buttons. If the view hides "Lock topic" from members, that is only to reduce noise. The admin route group and write service must still reject the action server-side.

## Step: Add Admin Moderation UI

Admin users should see moderation forms near the content they can moderate.

```html
<?php if ($permissions['topic.lock'] ?? false): ?>
    <form method="POST" action="/forum-demo/admin/topics">
        <?= \CorianderCore\Core\Security\Csrf::input() ?>
        <input type="hidden" name="return_to" value="topic">
        <input type="hidden" name="topic_id" value="<?= (int) $topic['id'] ?>">
        <button name="action" value="lock">Lock topic</button>
    </form>
<?php endif; ?>
```

Use the same pattern for reply moderation with `/forum-demo/admin/replies`. Include both `return_to=topic` and `topic_id` when the form appears on a topic page.

Moderation POST routes are action endpoints, not pages users should land on. The hidden `return_to` field tells the controller which GET page should receive the flash message after the action:

- `return_to=topic` keeps the admin on the topic detail page.
- `return_to=admin` keeps the admin on the moderation queue.

This makes the UI feel stable. Clicking "Hide reply" or "Lock topic" should not move the user away from the content they are reviewing, and the redirect prevents form resubmission warnings.

## Step: Show Write Feedback

When a write succeeds, show the result message returned by the write service. In public demo read-only mode, that message must be explicit that nothing was saved.

```html
<?php if (($flash['ok'] ?? false) === true): ?>
    <p><?= htmlspecialchars($flash['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
<?php endif; ?>
```

Use the same flash shape for topic, reply, and admin actions.

Keep flash rendering close to the page heading so users notice the result without losing their place in the forum content.

## Step: Keep JavaScript Optional

TypeScript can improve interactions, but the core demo should work as server-rendered HTML. Add TypeScript for small enhancements only.

```structure
nodejs/src/forum-demo/index.ts
public/assets/js/forum-demo/index.js
```

## Checkpoint

Open [/forum-demo/topics](/forum-demo/topics) as a guest, member, and admin. The content should stay readable, while forms and admin links adapt to the user.

## Common Mistakes

- Putting permission rules directly in the template.
- Rendering unsanitized strings.
- Making JavaScript required for basic navigation or form submission.

## Next

Continue with [Demo Authentication](/guided-projects/forum/authentication).
