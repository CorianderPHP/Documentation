<?php
$users = $users ?? [];
$flash = $flash ?? null;
$forumGuideReturn = $forumGuideReturn ?? '/guided-projects/forum';
?>
<section class="forum-demo-theme relative px-4 py-8 font-poppins sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-dark-green dark:text-mint">
        <a href="/forum-demo/admin">Admin moderation</a>
        <span class="text-black/35 dark:text-white/35">/</span>
        <a href="<?= htmlspecialchars($forumGuideReturn, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Back to guide step</a>
    </div>
    <h1 class="mt-4 font-concert-one text-4xl text-dark-green dark:text-mint md:text-5xl">Manage users</h1>

    <?php if ($flash): ?>
        <div data-demo-flash class="mt-6 border <?= $flash['ok'] ? 'border-dark-green/25 bg-dark-green/10 text-dark-green dark:border-mint/30 dark:bg-mint/10 dark:text-mint' : 'border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-200' ?> p-4"><?= htmlspecialchars($flash['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="mt-8 divide-y divide-dark-green/10 border-y border-dark-green/10 dark:divide-mint/10 dark:border-mint/15">
        <?php foreach ($users as $user): ?>
            <div class="grid gap-3 py-5 md:grid-cols-[1fr_16rem] md:items-center">
                <div>
                    <h2 class="font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($user['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    <p class="mt-1 text-sm text-black/70 dark:text-white/70"><?= htmlspecialchars($user['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> - <?= htmlspecialchars($user['role'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                </div>
                <form method="POST" action="/forum-demo/admin/users" data-demo-form class="flex flex-col gap-2 sm:flex-row">
                    <?= \CorianderCore\Core\Security\Csrf::input() ?>
                    <input type="hidden" name="name" value="<?= htmlspecialchars($user['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <select name="role" class="min-w-0 flex-1 rounded-md border border-dark-green/20 bg-white px-3 py-2 text-sm text-black dark:border-mint/30 dark:bg-black dark:text-white">
                        <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                        <option value="moderator" <?= $user['role'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <button class="rounded-md bg-dark-green px-3 py-2 text-sm font-semibold text-true-white dark:bg-mint dark:text-black">Update</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>
