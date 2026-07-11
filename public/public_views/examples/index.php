<?php
/** @var array<int,array{project:\Modules\Docs\GuidedProject,pages:array<int,\Modules\Docs\DocumentationPage>}> $projects */
$projects = $projects ?? [];
?>

<section class="px-4 py-10 font-poppins sm:px-6 lg:px-8">
    <div class="border-b border-dark-green/10 pb-10 dark:border-mint/15">
        <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">Guided projects</p>
        <h1 class="mt-3 max-w-3xl font-concert-one text-4xl leading-tight text-dark-green dark:text-mint sm:text-5xl md:text-6xl">Learn by building complete features.</h1>
        <p class="mt-5 max-w-3xl text-lg leading-8 text-black/70 dark:text-white/70">
            Guided projects are complete builds, separate from the framework reference. Use them when you want to see routes, controllers, modules, middleware, views, assets, and persistence decisions working together.
        </p>
    </div>

    <div class="mt-10 divide-y divide-dark-green/10 dark:divide-mint/10">
        <?php foreach ($projects as $projectEntry): ?>
            <?php $project = $projectEntry['project']; ?>
            <a href="<?= htmlspecialchars($project->basePath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="grid gap-4 px-3 py-6 hover:bg-black/5 md:grid-cols-[16rem_1fr_auto] md:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45"><?= htmlspecialchars($project->listEyebrow, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    <h2 class="mt-1 font-concert-one text-3xl text-dark-green dark:text-mint"><?= htmlspecialchars($project->listTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-black/55 dark:text-white/55">
                        <?php foreach ($project->listTags as $tag): ?>
                            <span class="rounded-full border border-dark-green/15 px-2 py-1 dark:border-mint/20"><?= htmlspecialchars($tag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <p class="max-w-2xl text-sm leading-6 text-black/65 dark:text-white/65">
                    <?= htmlspecialchars($project->listDescription, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </p>
                <span class="font-semibold text-dark-green dark:text-mint">Open guide</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
