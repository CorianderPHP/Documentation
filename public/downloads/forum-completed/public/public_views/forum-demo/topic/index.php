<?php
$topic = $topic ?? null;
$replies = $replies ?? [];
$permissions = $permissions ?? [];
$flash = $flash ?? null;
$forumGuideReturn = $forumGuideReturn ?? '/guided-projects/forum';
$canModerateTopic = $permissions['topic.lock'] ?? false;
$canModerateReply = $permissions['reply.moderate'] ?? false;
?>
<section class="forum-demo-theme relative px-4 py-8 font-poppins sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-dark-green dark:text-mint">
        <a href="/forum-demo/topics">Back to topics</a>
        <span class="text-black/35 dark:text-white/35">/</span>
        <a href="<?= htmlspecialchars($forumGuideReturn, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Back to guide step</a>
    </div>
    <div class="mt-4 border-b border-dark-green/15 pb-6 dark:border-mint/20">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="text-sm font-semibold text-black/50 dark:text-white/50"><?= htmlspecialchars($topic['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <h1 class="mt-2 font-concert-one text-4xl text-dark-green dark:text-mint md:text-5xl"><?= htmlspecialchars($topic['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-black/55 dark:text-white/55">
                    <span class="rounded-full border border-dark-green/15 px-2 py-1 dark:border-mint/20"><?= htmlspecialchars($topic['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <span class="rounded-full border border-dark-green/15 px-2 py-1 dark:border-mint/20"><?= (int) $topic['replies'] ?> replies</span>
                    <span class="rounded-full border border-dark-green/15 px-2 py-1 dark:border-mint/20">Updated <?= htmlspecialchars($topic['updated_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                </div>
            </div>
            <?php if ($canModerateTopic): ?>
                <form method="POST" action="/forum-demo/admin/topics" data-demo-form class="flex w-full flex-wrap gap-2 md:w-auto">
                    <?= \CorianderCore\Core\Security\Csrf::input() ?>
                    <input type="hidden" name="return_to" value="topic">
                    <input type="hidden" name="topic_id" value="<?= (int) $topic['id'] ?>">
                    <input type="hidden" name="title" value="<?= htmlspecialchars($topic['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <button name="action" value="<?= $topic['locked'] ? 'unlock' : 'lock' ?>" class="flex-1 rounded-md border border-dark-green/25 px-3 py-2 text-sm font-semibold text-dark-green dark:border-mint/30 dark:text-mint md:flex-none"><?= $topic['locked'] ? 'Unlock topic' : 'Lock topic' ?></button>
                    <button name="action" value="hide" class="flex-1 rounded-md bg-dark-green px-3 py-2 text-sm font-semibold text-true-white dark:bg-mint dark:text-black md:flex-none">Hide topic</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($flash): ?>
        <div data-demo-flash class="mt-6 border <?= $flash['ok'] ? 'border-dark-green/25 bg-dark-green/10 text-dark-green dark:border-mint/30 dark:bg-mint/10 dark:text-mint' : 'border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-200' ?> p-4"><?= htmlspecialchars($flash['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
        <section class="space-y-6">
            <article class="border-b border-dark-green/10 pb-6 dark:border-mint/15">
                <div class="grid gap-4 md:grid-cols-[11rem_1fr]">
                    <aside class="text-sm">
                        <p class="font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($topic['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <p class="mt-1 capitalize text-black/55 dark:text-white/55"><?= htmlspecialchars($topic['role'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <p class="mt-3 text-black/45 dark:text-white/45"><?= htmlspecialchars($topic['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    </aside>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45">Original post</p>
                        <p class="mt-3 whitespace-pre-line leading-8 text-black/80 dark:text-white/80"><?= htmlspecialchars($topic['body'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    </div>
                </div>
            </article>

            <div>
                <h2 class="font-concert-one text-3xl text-dark-green dark:text-mint">Replies</h2>
                <div class="mt-4 divide-y divide-dark-green/10 border-y border-dark-green/10 dark:divide-mint/10 dark:border-mint/15">
                    <?php foreach ($replies as $reply): ?>
                        <article class="grid gap-4 py-5 md:grid-cols-[11rem_1fr_auto]">
                            <aside class="text-sm">
                                <p class="font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($reply['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                                <p class="mt-1 capitalize text-black/55 dark:text-white/55"><?= htmlspecialchars($reply['role'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                                <p class="mt-3 text-black/45 dark:text-white/45"><?= htmlspecialchars($reply['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                            </aside>
                            <div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full border border-dark-green/15 px-2 py-0.5 text-xs font-semibold dark:border-mint/20"><?= htmlspecialchars($reply['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                </div>
                                <p class="mt-3 leading-7 text-black/75 dark:text-white/75"><?= htmlspecialchars($reply['body'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                            </div>
                            <?php if ($canModerateReply): ?>
                                <form method="POST" action="/forum-demo/admin/replies" data-demo-form class="md:text-right">
                                    <?= \CorianderCore\Core\Security\Csrf::input() ?>
                                    <input type="hidden" name="return_to" value="topic">
                                    <input type="hidden" name="topic_id" value="<?= (int) $topic['id'] ?>">
                                    <input type="hidden" name="reply_id" value="<?= (int) $reply['id'] ?>">
                                    <input type="hidden" name="body" value="<?= htmlspecialchars($reply['body'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                    <button name="action" value="hide" class="w-full rounded-md border border-dark-green/25 px-3 py-2 text-sm font-semibold text-dark-green dark:border-mint/30 dark:text-mint md:w-auto">Hide reply</button>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <aside class="border-t border-dark-green/10 pt-6 dark:border-mint/15 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
            <h2 class="font-concert-one text-2xl text-dark-green dark:text-mint">Reply</h2>
            <?php if (!($permissions['reply.create'] ?? false)): ?>
                <p class="mt-3 text-sm leading-6 text-black/70 dark:text-white/70">Log in to test the reply flow. The public demo validates the action without saving it.</p>
                <a href="/forum-demo/login" class="mt-4 inline-flex rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-mint dark:text-black">Log in</a>
            <?php elseif ($topic['locked']): ?>
                <p class="mt-3 text-sm leading-6 text-black/70 dark:text-white/70">This topic is locked. Admins can unlock it from the moderation controls.</p>
            <?php else: ?>
                <form method="POST" action="/forum-demo/topics/<?= (int) $topic['id'] ?>/replies" data-demo-form class="mt-4 space-y-4">
                    <?= \CorianderCore\Core\Security\Csrf::input() ?>
                    <textarea name="body" rows="5" class="w-full rounded-md border border-dark-green/20 bg-white px-3 py-2 text-black dark:border-mint/30 dark:bg-black dark:text-white" placeholder="Write a reply"></textarea>
                    <button class="w-full rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-mint dark:text-black">Reply</button>
                </form>
            <?php endif; ?>
        </aside>
    </div>
</section>
