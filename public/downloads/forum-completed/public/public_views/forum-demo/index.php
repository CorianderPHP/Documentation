<?php
/** @var array<int,array{id:int,title:string,author:string,role:string,replies:int,locked:bool,status:string,excerpt:string,category:string,updated_at:string}> $topics */
$currentUser = $currentUser ?? null;
$permissions = $permissions ?? [];
$forumGuideReturn = $forumGuideReturn ?? '/guided-projects/forum';
?>

<section class="forum-demo-theme relative px-4 py-8 font-poppins sm:px-6 lg:px-8">
    <div class="border-b border-dark-green/15 pb-8 dark:border-mint/20">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">Demo environment</p>
                <h1 class="mt-3 font-concert-one text-4xl text-dark-green dark:text-mint md:text-6xl">Forum with permissions</h1>
                <p class="mt-4 max-w-3xl text-lg leading-8 text-black/75 dark:text-white/75">Read topics, switch demo accounts, and try permission-gated actions. Public writes validate successfully but are not persisted.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($forumGuideReturn, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="rounded-md border border-dark-green px-4 py-2 font-semibold text-dark-green dark:border-mint dark:text-mint">Back to guide step</a>
                <?php if ($currentUser): ?>
                    <form method="POST" action="/forum-demo/logout">
                        <?= \CorianderCore\Core\Security\Csrf::input() ?>
                        <button class="rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-mint dark:text-black">Log out</button>
                    </form>
                <?php else: ?>
                    <a href="/forum-demo/login" class="rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-mint dark:text-black">Log in</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6 grid gap-3 border-y border-dark-green/10 py-4 text-sm dark:border-mint/15 md:grid-cols-4">
            <div>
                <span class="block text-black/50 dark:text-white/50">Current account</span>
                <strong><?= $currentUser ? htmlspecialchars($currentUser['name'] . ' (' . $currentUser['role'] . ')', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'Guest' ?></strong>
            </div>
            <div>
                <span class="block text-black/50 dark:text-white/50">Create topics</span>
                <strong><?= ($permissions['topic.create'] ?? false) ? 'Allowed' : 'Blocked' ?></strong>
            </div>
            <div>
                <span class="block text-black/50 dark:text-white/50">Reply</span>
                <strong><?= ($permissions['reply.create'] ?? false) ? 'Allowed' : 'Blocked' ?></strong>
            </div>
            <div>
                <span class="block text-black/50 dark:text-white/50">Moderate</span>
                <strong><?= ($permissions['admin.view'] ?? false) ? 'Allowed' : 'Blocked' ?></strong>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <section>
            <div class="flex items-center justify-between gap-4 border-b border-dark-green/10 pb-3 dark:border-mint/15">
                <h2 class="font-concert-one text-3xl text-dark-green dark:text-mint">Latest topics</h2>
                <a href="/forum-demo/topics" class="text-sm font-semibold text-dark-green dark:text-mint">View all</a>
            </div>

            <div class="divide-y divide-dark-green/10 dark:divide-mint/10">
                <?php foreach ($topics as $topic): ?>
                    <a href="/forum-demo/topics/<?= (int) $topic['id'] ?>" class="grid gap-3 px-2 py-5 transition hover:bg-black/5 md:grid-cols-[1fr_8rem_6rem] md:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($topic['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h3>
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
    </div>
</section>
