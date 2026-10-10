<?php
/*
 * Rendered by ForumActions::admin().
 * The route group is protected by ForumDemoAdminMiddleware before this view is
 * reached. Forms still post to write actions that check permissions again.
 */
$users = $users ?? [];
$categories = $categories ?? [];
$topics = $topics ?? [];
$moderationQueue = $moderationQueue ?? [];
$flash = $flash ?? null;
$forumGuideReturn = $forumGuideReturn ?? '/guided-projects/forum';
?>
<section class="forum-demo-theme relative px-4 py-8 font-poppins sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-dark-green dark:text-mint">
        <a href="/forum-demo">Forum demo</a>
        <span class="text-black/35 dark:text-white/35">/</span>
        <a href="<?= $forumGuideReturn ?>">Back to guide step</a>
    </div>
    <div class="mt-4 border-b border-dark-green/15 pb-6 dark:border-mint/20">
        <h1 class="font-concert-one text-4xl text-dark-green dark:text-mint md:text-5xl">Admin moderation</h1>
        <p class="mt-3 max-w-3xl text-black/75 dark:text-white/75">This area is protected by project middleware. Admins can try topic locks, reply hiding, and user role changes without storing public visitor content.</p>
    </div>

    <?php if ($flash): ?>
        <div data-demo-flash class="mt-6 border <?= $flash['ok'] ? 'border-dark-green/25 bg-dark-green/10 text-dark-green dark:border-mint/30 dark:bg-mint/10 dark:text-mint' : 'border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-200' ?> p-4"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <div class="mt-8 grid gap-4 md:grid-cols-3">
        <div class="border-y border-dark-green/10 py-4 dark:border-mint/15">
            <span class="text-sm text-black/50 dark:text-white/50">Users</span>
            <strong class="mt-1 block text-2xl text-dark-green dark:text-mint"><?= count($users) ?></strong>
        </div>
        <div class="border-y border-dark-green/10 py-4 dark:border-mint/15">
            <span class="text-sm text-black/50 dark:text-white/50">Topics</span>
            <strong class="mt-1 block text-2xl text-dark-green dark:text-mint"><?= count($topics) ?></strong>
        </div>
        <div class="border-y border-dark-green/10 py-4 dark:border-mint/15">
            <span class="text-sm text-black/50 dark:text-white/50">Categories</span>
            <strong class="mt-1 block text-2xl text-dark-green dark:text-mint"><?= count($categories) ?></strong>
        </div>
    </div>

    <section class="mt-10">
        <div class="flex items-center justify-between gap-4 border-b border-dark-green/10 pb-3 dark:border-mint/15">
            <h2 class="font-concert-one text-3xl text-dark-green dark:text-mint">Moderation queue</h2>
            <a href="/forum-demo/admin/users" class="text-sm font-semibold text-dark-green dark:text-mint">Manage users</a>
        </div>
        <div class="divide-y divide-dark-green/10 dark:divide-mint/10">
            <?php foreach ($moderationQueue as $item): ?>
                <div class="grid gap-3 py-5 md:grid-cols-[8rem_1fr_9rem_auto] md:items-center">
                    <span class="text-sm font-semibold capitalize text-dark-green dark:text-mint"><?= $item['type'] ?></span>
                    <div>
                        <p class="font-semibold"><?= $item['title'] ?></p>
                        <p class="mt-1 text-sm text-black/65 dark:text-white/65"><?= $item['subject'] ?></p>
                        <p class="mt-1 text-xs text-black/45 dark:text-white/45">By <?= $item['author'] ?></p>
                    </div>
                    <span class="text-sm"><?= $item['status'] ?></span>
                    <form method="POST" action="/forum-demo/admin/<?= $item['type'] === 'reply' ? 'replies' : 'topics' ?>" data-demo-form>
                        <?= \CorianderCore\Core\Security\Csrf::input() ?>
                        <input type="hidden" name="return_to" value="admin">
                        <input type="hidden" name="<?= $item['type'] === 'reply' ? 'reply_id' : 'topic_id' ?>" value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="title" value="<?= $item['subject'] ?>">
                        <button name="action" value="<?= $item['action'] ?>" class="rounded-md border border-dark-green/25 px-3 py-2 text-sm font-semibold text-dark-green dark:border-mint/30 dark:text-mint"><?= ucfirst($item['action']) ?></button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</section>
