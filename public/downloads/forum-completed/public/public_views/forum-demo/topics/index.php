<?php
$topics = $topics ?? [];
$categories = $categories ?? [];
$permissions = $permissions ?? [];
$flash = $flash ?? null;
$forumGuideReturn = $forumGuideReturn ?? '/guided-projects/forum';
?>
<section class="forum-demo-theme relative px-4 py-8 font-poppins sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 border-b border-dark-green/15 pb-6 dark:border-mint/20 md:flex-row md:items-end md:justify-between">
        <div>
            <a href="/forum-demo" class="text-sm font-semibold text-dark-green dark:text-mint">Forum demo</a>
            <h1 class="mt-3 font-concert-one text-4xl text-dark-green dark:text-mint md:text-5xl">Topics</h1>
        </div>
        <a href="<?= htmlspecialchars($forumGuideReturn, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="rounded-md border border-dark-green px-4 py-2 font-semibold text-dark-green dark:border-mint dark:text-mint">Back to guide step</a>
    </div>

    <?php if ($flash): ?>
        <div data-demo-flash class="mt-6 border <?= $flash['ok'] ? 'border-dark-green/25 bg-dark-green/10 text-dark-green dark:border-mint/30 dark:bg-mint/10 dark:text-mint' : 'border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-200' ?> p-4"><?= htmlspecialchars($flash['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
        <section>
            <div class="hidden border-b border-dark-green/10 pb-2 text-xs font-semibold uppercase tracking-1 text-black/45 dark:border-mint/15 dark:text-white/45 md:grid md:grid-cols-[1fr_8rem_7rem]">
                <span>Topic</span>
                <span>Updated</span>
                <span>Replies</span>
            </div>
            <div class="divide-y divide-dark-green/10 border-b border-dark-green/10 dark:divide-mint/10 dark:border-mint/15">
                <?php foreach ($topics as $topic): ?>
                    <a href="/forum-demo/topics/<?= (int) $topic['id'] ?>" class="grid gap-3 py-5 transition hover:px-3 md:grid-cols-[1fr_8rem_7rem] md:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($topic['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                                <span class="rounded-full border border-dark-green/15 px-2 py-0.5 text-xs font-semibold dark:border-mint/20"><?= htmlspecialchars($topic['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                            </div>
                            <p class="mt-1 text-sm leading-6 text-black/65 dark:text-white/65"><?= htmlspecialchars($topic['excerpt'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                            <p class="mt-1 text-xs text-black/45 dark:text-white/45"><?= htmlspecialchars($topic['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> by <?= htmlspecialchars($topic['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        </div>
                        <span class="text-sm text-black/60 dark:text-white/60"><?= htmlspecialchars($topic['updated_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        <span class="text-sm font-semibold text-dark-green dark:text-mint"><?= (int) $topic['replies'] ?> replies</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="border-l border-dark-green/10 pl-5 dark:border-mint/15">
            <h2 class="font-concert-one text-2xl text-dark-green dark:text-mint">Create topic</h2>
            <?php if (!($permissions['topic.create'] ?? false)): ?>
                <p class="mt-3 text-sm leading-6 text-black/70 dark:text-white/70">Log in as the member or admin demo account to submit this form.</p>
                <a href="/forum-demo/login" class="mt-4 inline-flex rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-mint dark:text-black">Log in</a>
            <?php else: ?>
                <form method="POST" action="/forum-demo/topics" data-demo-form class="mt-4 space-y-4">
                    <?= \CorianderCore\Core\Security\Csrf::input() ?>
                    <label class="block">
                        <span class="text-sm font-semibold">Category</span>
                        <select name="category_id" class="mt-1 w-full rounded-md border border-dark-green/20 bg-white px-3 py-2 text-black dark:border-mint/30 dark:bg-black dark:text-white">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Title</span>
                        <input name="title" class="mt-1 w-full rounded-md border border-dark-green/20 bg-white px-3 py-2 text-black dark:border-mint/30 dark:bg-black dark:text-white" placeholder="How do migrations work?">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Original post</span>
                        <textarea name="body" rows="5" class="mt-1 w-full rounded-md border border-dark-green/20 bg-white px-3 py-2 text-black dark:border-mint/30 dark:bg-black dark:text-white"></textarea>
                    </label>
                    <button class="w-full rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-mint dark:text-black">Create topic</button>
                </form>
            <?php endif; ?>
        </aside>
    </div>
</section>
