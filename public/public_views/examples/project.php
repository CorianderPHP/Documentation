<?php
/** @var \Modules\Docs\GuidedProject $project */
/** @var array<int,\Modules\Docs\DocumentationPage> $pages */
/** @var array<string,array<int,array{page:\Modules\Docs\DocumentationPage,number:int,label:string,path:string}>> $navigationGroups */
/** @var \Modules\Docs\DocumentationPage|null $page */
/** @var array<int,array{page:\Modules\Docs\DocumentationPage,score:int,excerpt:string}> $results */
/** @var array{previous:?array{page:\Modules\Docs\DocumentationPage,path:string,label:string},next:?array{page:\Modules\Docs\DocumentationPage,path:string,label:string}} $adjacent */
$mode = $mode ?? 'show';
$pages = $pages ?? [];
$navigationGroups = $navigationGroups ?? [];
$page = $page ?? null;
$query = $query ?? '';
$results = $results ?? [];
$adjacent = $adjacent ?? ['previous' => null, 'next' => null];
$activeSlug = $page?->slug ?? 'search';
$demoReturnPath = $_SERVER['REQUEST_URI'] ?? $project->basePath;
$flatNavigation = [];
foreach ($navigationGroups as $groupItems) {
    foreach ($groupItems as $item) {
        $flatNavigation[] = $item;
    }
}
$totalSteps = count($flatNavigation);
$activeStep = null;
foreach ($flatNavigation as $item) {
    if ($page !== null && $item['page']->slug === $page->slug) {
        $activeStep = $item;
        break;
    }
}
?>

<section class="min-h-[calc(100vh-14rem)] px-4 py-8 font-poppins sm:px-6 lg:px-8">
    <div class="sticky top-0 md:top-16 z-40 -mx-4 mb-6 border-y border-dark-green/10 bg-white/95 px-4 py-3 backdrop-blur dark:border-mint/15 dark:bg-black/90 lg:hidden">
        <button type="button" class="flex w-full items-center justify-between gap-4 rounded-md border border-dark-green/15 bg-true-white px-4 py-3 text-left shadow-sm dark:border-mint/20 dark:bg-true-black" data-mobile-nav-open="<?= htmlspecialchars($project->key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>-mobile-nav" aria-controls="<?= htmlspecialchars($project->key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>-mobile-nav" aria-expanded="false">
            <span>
                <span class="block text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45">Browse project steps</span>
                <span class="mt-1 block font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($activeStep !== null ? (string) $activeStep['number'] . '. ' . $activeStep['label'] : ($mode === 'search' ? 'Search' : $project->title), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
            </span>
            <span aria-hidden="true" class="text-2xl leading-none text-dark-green dark:text-mint">+</span>
        </button>
    </div>

    <div id="<?= htmlspecialchars($project->key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>-mobile-nav" class="fixed inset-0 z-50 hidden lg:hidden" data-mobile-nav-drawer aria-hidden="true">
        <button type="button" class="absolute inset-0 bg-black/40" data-mobile-nav-close aria-label="Close guided project navigation"></button>
        <div class="y-slider relative h-full w-11/12 max-w-sm overflow-y-auto bg-white px-5 py-5 shadow-xl dark:bg-black">
            <div class="flex items-start justify-between gap-4 border-b border-dark-green/10 pb-5 dark:border-mint/15">
                <div>
                    <a href="/guided-projects" class="text-sm font-semibold text-dark-green dark:text-mint">Guided Projects</a>
                    <h2 class="mt-2 font-concert-one text-2xl text-dark-green dark:text-mint"><?= htmlspecialchars($project->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    <p class="mt-2 text-xs font-semibold text-black/45 dark:text-white/45"><?= $activeStep !== null ? 'Step ' . (int) $activeStep['number'] . ' of ' . (int) $totalSteps : 'Search' ?></p>
                </div>
                <button type="button" class="rounded-md border border-dark-green/15 px-3 py-2 text-sm font-semibold text-dark-green dark:border-mint/20 dark:text-mint" data-mobile-nav-close>Close</button>
            </div>

            <nav class="mt-6 space-y-5 text-sm">
                <?php foreach ($navigationGroups as $groupTitle => $items): ?>
                    <section>
                        <p class="px-2 text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45"><?= htmlspecialchars($groupTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <div class="mt-2 space-y-0.5">
                            <?php foreach ($items as $item): ?>
                                <?php $navPage = $item['page']; ?>
                                <a href="<?= htmlspecialchars($item['path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="grid grid-cols-[1.75rem_1fr] gap-2 border-l-2 px-3 py-2 <?= $activeSlug === $navPage->slug ? 'border-dark-green bg-dark-green/5 font-semibold text-dark-green dark:border-mint dark:bg-mint/10 dark:text-mint' : 'border-transparent text-black/70 hover:border-dark-green/30 hover:text-dark-green dark:text-white/70 dark:hover:border-mint/30 dark:hover:text-mint' ?>">
                                    <span class="font-semibold tabular-nums"><?= (int) $item['number'] ?>.</span>
                                    <span><?= htmlspecialchars($item['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <div class="grid gap-10 lg:grid-cols-[15rem_minmax(0,1fr)] xl:grid-cols-[15rem_minmax(0,1fr)_14rem]">
        <aside class="y-slider hidden overflow-y-auto border-b border-dark-green/10 pb-5 lg:sticky lg:top-24 lg:block lg:max-h-[calc(100vh-8rem)] lg:border-b-0 lg:pb-0 dark:border-mint/15" data-scroll-memory="<?= htmlspecialchars($project->scrollMemoryKey, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <div class="border-b border-dark-green/10 pb-4 dark:border-mint/15">
                <a href="/guided-projects" class="text-sm font-semibold text-dark-green dark:text-mint">Guided Projects</a>
                <h1 class="mt-2 font-concert-one text-2xl text-dark-green dark:text-mint"><?= htmlspecialchars($project->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
                <p class="mt-2 text-xs font-semibold text-black/45 dark:text-white/45"><?= $activeStep !== null ? 'Step ' . (int) $activeStep['number'] . ' of ' . (int) $totalSteps : 'Search' ?></p>
            </div>

            <nav class="mt-5 space-y-5 text-sm">
                <?php foreach ($navigationGroups as $groupTitle => $items): ?>
                    <section>
                        <p class="px-2 text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45"><?= htmlspecialchars($groupTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <div class="mt-2 space-y-0.5">
                            <?php foreach ($items as $item): ?>
                                <?php $navPage = $item['page']; ?>
                                <a href="<?= htmlspecialchars($item['path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="grid grid-cols-[1.75rem_1fr] gap-2 border-l-2 px-3 py-1.5 <?= $activeSlug === $navPage->slug ? 'border-dark-green bg-dark-green/5 font-semibold text-dark-green dark:border-mint dark:bg-mint/10 dark:text-mint' : 'border-transparent text-black/70 hover:border-dark-green/30 hover:text-dark-green dark:text-white/70 dark:hover:border-mint/30 dark:hover:text-mint' ?>">
                                    <span class="font-semibold tabular-nums"><?= (int) $item['number'] ?>.</span>
                                    <span><?= htmlspecialchars($item['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="min-w-0">
            <header class="border-b border-dark-green/10 pb-6 dark:border-mint/15">
                <nav class="text-sm text-black/50 dark:text-white/50">
                    <a href="/guided-projects" class="hover:text-dark-green dark:hover:text-mint">Guided Projects</a>
                    <span class="mx-2">/</span>
                    <a href="<?= htmlspecialchars($project->basePath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="hover:text-dark-green dark:hover:text-mint"><?= htmlspecialchars($project->navTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                    <?php if ($page !== null && $page->slug !== $project->indexSlug()): ?>
                        <span class="mx-2">/</span>
                        <span><?= htmlspecialchars($page->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <?php endif; ?>
                </nav>

                <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_20rem] xl:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint"><?= htmlspecialchars($project->eyebrow, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <h1 class="mt-2 font-concert-one text-3xl text-dark-green dark:text-mint sm:text-4xl md:text-5xl"><?= htmlspecialchars($mode === 'search' ? 'Search the project' : ($page?->title ?? $project->title), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-black/55 dark:text-white/55">
                            <?php if ($activeStep !== null): ?>
                                <span class="rounded-full border border-dark-green/15 px-2 py-1 dark:border-mint/20">Step <?= (int) $activeStep['number'] ?> / <?= (int) $totalSteps ?></span>
                            <?php endif; ?>
                            <?php foreach ($project->detailTags as $tag): ?>
                                <span class="rounded-full border border-dark-green/15 px-2 py-1 dark:border-mint/20"><?= htmlspecialchars($tag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <form action="<?= htmlspecialchars($project->searchPath(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="GET">
                            <label for="<?= htmlspecialchars($project->key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>-search" class="sr-only">Search project guide</label>
                            <input id="<?= htmlspecialchars($project->key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>-search" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-dark-green/15 bg-true-white px-3 py-2 text-sm text-black shadow-sm outline-none focus:border-dark-green dark:border-mint/20 dark:bg-true-black dark:text-white dark:focus:border-mint" placeholder="<?= htmlspecialchars($project->searchPlaceholder, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
                        </form>
                        <div class="flex flex-wrap gap-2">
                            <?php if ($project->liveDemo !== null): ?>
                                <?php $livePath = $project->liveDemo['path'] . ($project->liveDemo['withReturn'] ? '?from=' . urlencode($demoReturnPath) : ''); ?>
                                <a href="<?= htmlspecialchars($livePath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="rounded-md bg-dark-green px-3 py-2 text-sm font-semibold text-true-white transition hover:-translate-y-0.5 hover:bg-dark-green/90 focus:outline-none focus:ring-2 focus:ring-dark-green/30 dark:bg-mint dark:text-black dark:hover:bg-mint/90 dark:focus:ring-mint/30"><?= htmlspecialchars($project->liveDemo['cta'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                            <?php endif; ?>
                            <?php foreach ($project->headerActions as $action): ?>
                                <a href="<?= htmlspecialchars($action['path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="rounded-md bg-dark-green px-3 py-2 text-sm font-semibold text-true-white transition hover:-translate-y-0.5 hover:bg-dark-green/90 focus:outline-none focus:ring-2 focus:ring-dark-green/30 dark:bg-mint dark:text-black dark:hover:bg-mint/90 dark:focus:ring-mint/30"><?= htmlspecialchars($action['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                            <?php endforeach; ?>
                            <a href="#project-downloads" class="rounded-md border border-dark-green/20 px-3 py-2 text-sm font-semibold text-dark-green transition hover:-translate-y-0.5 hover:border-dark-green hover:bg-dark-green/5 focus:outline-none focus:ring-2 focus:ring-dark-green/20 dark:border-mint/25 dark:text-mint dark:hover:border-mint dark:hover:bg-mint/10 dark:focus:ring-mint/20"><?= htmlspecialchars($project->downloadLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                        </div>
                    </div>
                </div>
            </header>

            <?php if ($mode === 'search'): ?>
                <div class="mt-8 border-b border-dark-green/10 pb-6 dark:border-mint/15">
                    <h2 class="font-concert-one text-3xl text-dark-green dark:text-mint">Results for "<?= htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"</h2>
                </div>
                <div class="mt-8 divide-y divide-dark-green/10 dark:divide-mint/10">
                    <?php if ($query === ''): ?>
                        <p class="py-5 text-black/70 dark:text-white/70"><?= htmlspecialchars($project->emptySearchText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    <?php elseif ($results === []): ?>
                        <p class="py-5 text-black/70 dark:text-white/70"><?= htmlspecialchars($project->noResultsText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    <?php else: ?>
                        <?php foreach ($results as $result): ?>
                            <a href="<?= htmlspecialchars($project->pathForSlug($result['page']->slug), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="block py-5">
                                <h2 class="font-concert-one text-2xl text-dark-green dark:text-mint"><?= htmlspecialchars($result['page']->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                                <p class="mt-2 text-sm leading-6 text-black/65 dark:text-white/65"><?= htmlspecialchars($result['excerpt'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php elseif ($page !== null): ?>
                <article class="mt-8 max-w-4xl">
                    <?= $page->html ?>
                </article>
                <?php if ($project->liveDemo !== null): ?>
                    <?php $livePath = $project->liveDemo['path'] . ($project->liveDemo['withReturn'] ? '?from=' . urlencode($demoReturnPath) : ''); ?>
                    <div class="mt-12 border-t border-dark-green/10 pt-6 dark:border-mint/15">
                        <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">Live version</p>
                        <h2 class="mt-2 font-concert-one text-3xl text-dark-green dark:text-mint"><?= htmlspecialchars($project->liveDemo['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                        <p class="mt-2 max-w-3xl text-black/70 dark:text-white/70"><?= htmlspecialchars($project->liveDemo['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <a href="<?= htmlspecialchars($livePath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="mt-4 inline-flex rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white transition hover:-translate-y-0.5 hover:bg-dark-green/90 focus:outline-none focus:ring-2 focus:ring-dark-green/30 dark:bg-mint dark:text-black dark:hover:bg-mint/90 dark:focus:ring-mint/30"><?= htmlspecialchars($project->liveDemo['cta'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                    </div>
                <?php endif; ?>
                <?php if (isset($project->extraSections[$page->slug])): ?>
                    <?php
                    $extraView = PROJECT_ROOT . '/public/public_views/' . $project->extraSections[$page->slug] . '.php';
                    if (is_file($extraView)) {
                        require $extraView;
                    }
                    ?>
                <?php endif; ?>
                <div id="project-downloads" class="mt-8 border-t border-dark-green/10 pt-6 dark:border-mint/15">
                    <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">Project files</p>
                    <h2 class="mt-2 font-concert-one text-3xl text-dark-green dark:text-mint"><?= htmlspecialchars($project->downloadTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    <p class="mt-2 max-w-3xl text-black/70 dark:text-white/70"><?= htmlspecialchars($project->downloadDescription, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    <a href="<?= htmlspecialchars($project->downloadPath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="mt-4 inline-flex rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white transition hover:-translate-y-0.5 hover:bg-dark-green/90 focus:outline-none focus:ring-2 focus:ring-dark-green/30 dark:bg-mint dark:text-black dark:hover:bg-mint/90 dark:focus:ring-mint/30"><?= htmlspecialchars($project->downloadLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                </div>
                <nav class="mt-8 grid gap-3 border-t border-dark-green/10 pt-6 dark:border-mint/15 md:grid-cols-2">
                    <?php if ($adjacent['previous'] !== null): ?>
                        <a href="<?= htmlspecialchars($adjacent['previous']['path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="block rounded-md border border-dark-green/15 p-4 text-sm hover:border-dark-green dark:border-mint/20 dark:hover:border-mint">
                            <span class="text-black/45 dark:text-white/45">Previous</span>
                            <span class="mt-1 block font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($adjacent['previous']['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($adjacent['next'] !== null): ?>
                        <a href="<?= htmlspecialchars($adjacent['next']['path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="block rounded-md border border-dark-green/15 p-4 text-sm hover:border-dark-green dark:border-mint/20 dark:hover:border-mint <?= $adjacent['previous'] === null ? 'md:col-start-2' : '' ?>">
                            <span class="text-black/45 dark:text-white/45">Next</span>
                            <span class="mt-1 block font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($adjacent['next']['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>

        <aside class="hidden xl:block">
            <?php if ($page !== null && $page->headings !== []): ?>
                <div class="y-slider sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto border-l border-dark-green/10 pl-4 pr-2 dark:border-mint/15" data-scroll-memory="<?= htmlspecialchars($project->key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>-page-nav">
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
            <?php endif; ?>
        </aside>
    </div>
</section>
