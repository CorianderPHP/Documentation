<?php
/** @var \Modules\Docs\DocumentationPage $page */
$page = $page ?? null;
?>

<section class="px-4 py-10 font-poppins sm:px-6 lg:px-8">
    <div class="grid gap-10 xl:grid-cols-[minmax(0,1fr)_15rem]">
        <article class="max-w-5xl">
            <nav class="mb-6 text-sm text-black/50 dark:text-white/50">
                <a href="/home" class="hover:text-dark-green dark:hover:text-mint">Home</a>
                <span class="mx-2">/</span>
                <span>Start</span>
            </nav>
            <?= $page?->html ?>
        </article>

        <?php if ($page !== null && $page->headings !== []): ?>
            <aside class="hidden xl:block">
                <div class="y-slider sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto border-l border-dark-green/10 pl-4 pr-2 dark:border-mint/15">
                    <p class="text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45">On this page</p>
                    <nav class="mt-3 space-y-2 text-sm">
                        <?php foreach ($page->headings as $heading): ?>
                            <?php if ($heading['level'] > 3) continue; ?>
                            <a href="#<?= htmlspecialchars($heading['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="block <?= $heading['level'] === 1 ? '' : 'pl-3' ?> text-black/60 hover:text-dark-green dark:text-white/60 dark:hover:text-mint">
                                <?= htmlspecialchars($heading['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </aside>
        <?php endif; ?>
    </div>
</section>
