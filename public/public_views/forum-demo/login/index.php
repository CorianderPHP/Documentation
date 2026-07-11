<?php
$error = $error ?? null;
$demoAccounts = $demoAccounts ?? [];
$forumGuideReturn = $forumGuideReturn ?? '/guided-projects/forum';
?>
<section class="forum-demo-theme relative mx-auto max-w-4xl px-4 py-10 font-poppins sm:px-6">
    <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-dark-green dark:text-peach">
        <a href="/forum-demo">Back to forum demo</a>
        <span class="text-black/35 dark:text-white/35">/</span>
        <a href="<?= htmlspecialchars($forumGuideReturn, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Back to guide step</a>
    </div>
    <h1 class="mt-4 font-concert-one text-4xl text-dark-green dark:text-peach md:text-5xl">Demo login</h1>
    <p class="mt-3 text-black/75 dark:text-white/75">Use one of the fixed demo accounts. These accounts are only for exploring permissions and protected demo writes.</p>

    <?php if ($error): ?>
        <div class="mt-6 rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-red-700 dark:text-red-200"><?= $error ?></div>
    <?php endif; ?>

    <div class="mt-8 grid gap-5 md:grid-cols-2">
        <?php foreach ($demoAccounts as $account): ?>
            <form method="POST" action="/forum-demo/login" class="rounded-lg border border-dark-green/15 bg-true-white p-5 shadow-sm dark:border-peach/20 dark:bg-true-black">
                <?= \CorianderCore\Core\Security\Csrf::input() ?>
                <input type="hidden" name="quick_role" value="<?= $account['role'] ?>">
                <h2 class="font-concert-one text-3xl text-dark-green dark:text-peach"><?= $account['label'] ?></h2>
                <dl class="mt-4 space-y-2 text-sm text-black/70 dark:text-white/70">
                    <div><dt class="inline font-semibold">Email:</dt> <dd class="inline"><?= $account['email'] ?></dd></div>
                    <div><dt class="inline font-semibold">Password:</dt> <dd class="inline"><?= $account['password'] ?></dd></div>
                </dl>
                <button class="mt-5 w-full rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-peach dark:text-black">Use <?= $account['label'] ?></button>
            </form>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="/forum-demo/login" class="mt-8 rounded-lg border border-dark-green/15 bg-true-white p-5 shadow-sm dark:border-peach/20 dark:bg-true-black">
        <?= \CorianderCore\Core\Security\Csrf::input() ?>
        <h2 class="font-concert-one text-3xl text-dark-green dark:text-peach">Manual credentials</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="text-sm font-semibold">Email</span>
                <input name="email" type="email" class="mt-1 w-full rounded-md border border-dark-green/20 bg-white px-3 py-2 text-black dark:border-peach/30 dark:bg-black dark:text-white">
            </label>
            <label class="block">
                <span class="text-sm font-semibold">Password</span>
                <input name="password" type="password" class="mt-1 w-full rounded-md border border-dark-green/20 bg-white px-3 py-2 text-black dark:border-peach/30 dark:bg-black dark:text-white">
            </label>
        </div>
        <button class="mt-5 rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-peach dark:text-black">Log in</button>
    </form>
</section>
