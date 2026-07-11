<?php
/** @var array<int,\Modules\Docs\DocumentationPage> $pages */
/** @var array<string,array<int,\Modules\Docs\DocumentationPage>> $groups */
/** @var array<int,array{page:\Modules\Docs\DocumentationPage,score:int,excerpt:string}> $results */
/** @var \Modules\Docs\DocumentationPage|null $page */
$mode = $mode ?? 'index';
$pages = $pages ?? [];
$groups = $groups ?? [];
$results = $results ?? [];
$activeSlug = $activeSlug ?? 'index';
$query = $query ?? '';
$scope = $scope ?? 'all';
$page = $page ?? null;
$searchScope = 'reference';
$resultGroups = [];
foreach ($results as $result) {
    $resultGroups[$result['page']->section] ??= [];
    $resultGroups[$result['page']->section][] = $result;
}
?>

<section class="min-h-[calc(100vh-14rem)] px-4 py-8 font-poppins sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-[17rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)_14rem]">
        <aside id="docs-sidebar" class="y-slider lg:sticky lg:top-24 lg:max-h-[calc(100vh-8rem)] lg:overflow-y-auto" data-scroll-memory="docs-sidebar">
            <div class="border-b border-dark-green/10 pb-5 dark:border-peach/15">
                <a href="/docs" class="font-concert-one text-3xl text-dark-green dark:text-peach">Documentation</a>
                <p class="mt-2 text-sm leading-6 text-black/60 dark:text-white/60">Focused reference pages for the framework pieces developers reach for most.</p>
            </div>

            <form action="/docs/search" method="GET" class="mt-5">
                <label for="docs-search" class="sr-only">Search documentation</label>
                <input id="docs-search" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-full rounded-md border border-dark-green/15 bg-true-white px-3 py-2 text-sm text-black shadow-sm outline-none focus:border-dark-green dark:border-peach/20 dark:bg-true-black dark:text-white dark:focus:border-peach" placeholder="Search docs..." />
                <input type="hidden" name="scope" value="<?= htmlspecialchars($searchScope, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <p class="mt-2 text-xs text-black/45 dark:text-white/45">
                    Searches framework reference pages only.
                </p>
            </form>

            <nav class="mt-7 space-y-6 text-sm">
                <?php foreach ($groups as $groupTitle => $groupPages): ?>
                    <div>
                        <p class="px-2 text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45"><?= htmlspecialchars($groupTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <div class="mt-2 space-y-0.5">
                            <?php foreach ($groupPages as $navPage): ?>
                                <a href="/docs/<?= htmlspecialchars($navPage->slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="block border-l-2 px-3 py-1.5 <?= $activeSlug === $navPage->slug ? 'border-dark-green text-dark-green dark:border-peach dark:text-peach' : 'border-transparent text-black/70 hover:border-dark-green/30 hover:text-dark-green dark:text-white/70 dark:hover:border-peach/30 dark:hover:text-peach' ?>">
                                    <?= htmlspecialchars($navPage->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="min-w-0">
            <?php if ($mode === 'index'): ?>
                <div class="border-b border-dark-green/10 pb-10 dark:border-peach/15">
                    <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-peach">CorianderPHP</p>
                    <h1 class="mt-3 max-w-3xl font-concert-one text-5xl leading-tight text-dark-green dark:text-peach md:text-6xl">Find answers fast, then see them in a real app.</h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-black/70 dark:text-white/70">
                        Use the framework reference for focused lookup: routes, controllers, middleware, views, database, modules, security, cache, and frontend tooling.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="/start" class="rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white shadow-sm dark:bg-peach dark:text-black">Start a project</a>
                        <a href="/guided-projects" class="rounded-md border border-dark-green/20 bg-true-white px-4 py-2 font-semibold text-dark-green shadow-sm dark:border-peach/25 dark:bg-true-black dark:text-peach">Guided projects</a>
                    </div>
                </div>

                <div class="mt-10 space-y-10">
                    <?php foreach ($groups as $groupTitle => $groupPages): ?>
                        <section>
                            <div class="flex items-end justify-between gap-4 border-b border-dark-green/10 pb-3 dark:border-peach/15">
                                <h2 class="font-concert-one text-3xl text-dark-green dark:text-peach"><?= htmlspecialchars($groupTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                                <?php if ($groupTitle === 'Project: Forum'): ?>
                                    <a href="/docs/projects/forum" class="text-sm font-semibold text-dark-green dark:text-peach">Start guide</a>
                                <?php endif; ?>
                            </div>
                            <div class="divide-y divide-dark-green/10 dark:divide-peach/10">
                                <?php foreach ($groupPages as $docPage): ?>
                                    <a href="/docs/<?= htmlspecialchars($docPage->slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="grid gap-2 py-4 md:grid-cols-[14rem_1fr]">
                                        <span class="font-semibold text-dark-green dark:text-peach"><?= htmlspecialchars($docPage->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                        <span class="text-sm leading-6 text-black/65 dark:text-white/65"><?= htmlspecialchars($docPage->description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($mode === 'search'): ?>
                <div class="border-b border-dark-green/10 pb-8 dark:border-peach/15">
                    <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-peach">Reference search</p>
                    <h1 class="mt-3 font-concert-one text-4xl text-dark-green dark:text-peach md:text-5xl">Results for "<?= htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"</h1>
                    <p class="mt-3 text-sm text-black/55 dark:text-white/55">Searches framework reference pages only. Use <a class="font-semibold text-dark-green dark:text-peach" href="/guided-projects/forum/search?q=<?= urlencode($query) ?>">forum guided project search</a> for the project guide.</p>
                    <div class="mt-5 flex flex-wrap gap-2 text-sm">
                        <a href="/start" class="rounded-md border border-dark-green/15 px-3 py-2 font-semibold text-dark-green dark:border-peach/20 dark:text-peach">Start guide</a>
                        <a href="/guided-projects/forum/search?q=<?= urlencode($query) ?>" class="rounded-md border border-dark-green/15 px-3 py-2 font-semibold text-dark-green dark:border-peach/20 dark:text-peach">Forum project search</a>
                    </div>
                </div>

                <div id="docs-search-results" class="mt-8 space-y-8">
                    <?php if ($query === ''): ?>
                        <p class="py-5 text-black/70 dark:text-white/70">Type a search term to find controllers, migrations, middleware, modules, views, and security notes.</p>
                    <?php elseif ($results === []): ?>
                        <p class="py-5 text-black/70 dark:text-white/70">No documentation pages matched this search scope.</p>
                    <?php else: ?>
                        <?php foreach ($resultGroups as $section => $sectionResults): ?>
                            <section>
                                <h2 class="border-b border-dark-green/10 pb-3 font-concert-one text-3xl text-dark-green dark:border-peach/15 dark:text-peach"><?= htmlspecialchars($section, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                                <div class="divide-y divide-dark-green/10 dark:divide-peach/10">
                                    <?php foreach ($sectionResults as $result): ?>
                                        <a href="/docs/<?= htmlspecialchars($result['page']->slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="block py-5">
                                            <h3 class="font-concert-one text-2xl text-dark-green dark:text-peach"><?= htmlspecialchars($result['page']->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h3>
                                            <p class="mt-2 text-sm leading-6 text-black/65 dark:text-white/65"><?= htmlspecialchars($result['excerpt'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php elseif ($page !== null): ?>
                <article class="max-w-4xl">
                    <nav class="mb-6 text-sm text-black/50 dark:text-white/50">
                        <a href="/docs" class="hover:text-dark-green dark:hover:text-peach">Documentation</a>
                        <span class="mx-2">/</span>
                        <span><?= htmlspecialchars($page->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    </nav>
                    <?= $page->html ?>
                </article>

                <?php if (str_starts_with($page->slug, 'projects/forum')): ?>
                    <div class="mt-12 border-t border-dark-green/10 pt-6 dark:border-peach/15">
                        <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-peach">Live project</p>
                        <h2 class="mt-2 font-concert-one text-3xl text-dark-green dark:text-peach">See this guide running</h2>
                        <p class="mt-2 max-w-3xl text-black/70 dark:text-white/70">The guided forum project uses a real SQLite database locally. The public live demo is protected so visitor-written content is not stored on this documentation site.</p>
                        <a href="/forum-demo" class="mt-4 inline-flex rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-peach dark:text-black">Open the forum demo</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <aside class="hidden xl:block">
            <?php if ($page !== null && $page->headings !== []): ?>
                <div class="y-slider sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto border-l border-dark-green/10 pl-4 pr-2 dark:border-peach/15" data-scroll-memory="docs-page-nav">
                    <p class="text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45">On this page</p>
                    <nav class="mt-3 space-y-2 text-sm">
                        <?php foreach ($page->headings as $heading): ?>
                            <?php if ($heading['level'] > 3) continue; ?>
                            <a href="#<?= htmlspecialchars($heading['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="block <?= $heading['level'] === 1 ? '' : 'pl-3' ?> text-black/60 hover:text-dark-green dark:text-white/60 dark:hover:text-peach">
                                <?= htmlspecialchars($heading['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
