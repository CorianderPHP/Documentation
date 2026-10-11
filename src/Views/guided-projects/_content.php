<?php
/** @var array<int,array{project:\App\Modules\Docs\GuidedProject,pages:array<int,\App\Modules\Docs\DocumentationPage>}> $projects */
$projects = $projects ?? [];
?>

<section class="px-4 py-10 font-poppins sm:px-6 lg:px-8">
    <div class="border-b border-dark-green/10 pb-10 dark:border-mint/15">
        <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">Guided projects</p>
        <h1 class="mt-3 max-w-3xl font-concert-one text-4xl leading-tight text-dark-green dark:text-mint sm:text-5xl md:text-6xl">Learn by building complete features.</h1>
        <p class="mt-5 max-w-3xl text-lg leading-8 text-black/70 dark:text-white/70">
            Guided projects are complete builds, separate from the framework reference. Use them when you want to see routes, handlers, modules, middleware, views, assets, and persistence decisions working together.
        </p>
    </div>

    <div class="mt-10 grid gap-4">
        <?php foreach ($projects as $projectEntry): ?>
            <?php $project = $projectEntry['project']; ?>
            <article class="rounded-md border border-dark-green/10 bg-true-white shadow-sm dark:border-mint/15 dark:bg-true-black">
                <a href="<?= htmlspecialchars($project->basePath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="grid gap-4 px-5 py-5 transition hover:bg-dark-green/5 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-dark-green/20 dark:hover:bg-mint/5 dark:focus:ring-mint/20 lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-start">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45"><?= htmlspecialchars($project->listEyebrow, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></p>
                        <h2 class="mt-1 font-concert-one text-3xl text-dark-green dark:text-mint"><?= htmlspecialchars($project->listTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></h2>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-black/55 dark:text-white/55">
                            <?php foreach ($project->listTags as $tag): ?>
                                <span class="rounded-full border border-dark-green/15 px-2 py-1 dark:border-mint/20"><?= htmlspecialchars($tag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <p class="max-w-3xl text-sm leading-6 text-black/65 dark:text-white/65">
                        <?= htmlspecialchars($project->listDescription, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>
                    </p>
                </a>
                <div class="flex flex-wrap gap-2 border-t border-dark-green/10 px-5 py-4 dark:border-mint/15">
                    <?php if ($project->liveDemo !== null): ?>
                        <a href="<?= htmlspecialchars($project->liveDemo['path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="rounded-md border border-dark-green/20 px-4 py-2 text-sm font-semibold text-dark-green transition hover:border-dark-green/30 hover:bg-dark-green/5 focus:outline-none focus:ring-2 focus:ring-dark-green/20 dark:border-mint/25 dark:text-mint dark:hover:border-mint/40 dark:hover:bg-mint/5 dark:focus:ring-mint/20"><?= htmlspecialchars($project->liveDemo['cta'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></a>
                    <?php endif; ?>
                    <?php foreach ($project->headerActions as $action): ?>
                        <a href="<?= htmlspecialchars($action['path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="rounded-md border border-dark-green/20 px-4 py-2 text-sm font-semibold text-dark-green transition hover:border-dark-green/30 hover:bg-dark-green/5 focus:outline-none focus:ring-2 focus:ring-dark-green/20 dark:border-mint/25 dark:text-mint dark:hover:border-mint/40 dark:hover:bg-mint/5 dark:focus:ring-mint/20"><?= htmlspecialchars($action['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></a>
                    <?php endforeach; ?>
                    <a href="<?= htmlspecialchars($project->downloadUrl(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="rounded-md border border-dark-green/20 px-4 py-2 text-sm font-semibold text-dark-green transition hover:border-dark-green/30 hover:bg-dark-green/5 focus:outline-none focus:ring-2 focus:ring-dark-green/20 dark:border-mint/25 dark:text-mint dark:hover:border-mint/40 dark:hover:bg-mint/5 dark:focus:ring-mint/20"><?= htmlspecialchars($project->downloadLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
