<?php
/** @var array<int,\App\Modules\Docs\DocumentationPage> $pages */
/** @var array<string,array<int,\App\Modules\Docs\DocumentationPage>> $groups */
/** @var array<int,array{page:\App\Modules\Docs\DocumentationPage,score:int,excerpt:string}> $results */
/** @var \App\Modules\Docs\DocumentationPage|null $article */
$mode = $mode ?? 'index';
$pages = $pages ?? [];
$groups = $groups ?? [];
$results = $results ?? [];
$activeSlug = $activeSlug ?? 'index';
$query = $query ?? '';
$scope = $scope ?? 'all';
$article = $article ?? null;
$searchScope = 'reference';
$searchSuggestions = ['install', 'cli help', 'handlers', 'routes', 'middleware', 'migrations', 'images', 'modules', 'production', 'debugging'];
$quickAnswer = $results[0] ?? null;
if ($quickAnswer !== null && $quickAnswer['snippet'] === null) {
    $quickAnswer = null;
}
$renderDocumentationSearch = static function (string $query, string $searchScope, array $suggestions): void {
    ?>
    <form action="/documentation/search" method="GET" class="mb-8 border-b border-dark-green/10 pb-6 dark:border-mint/15">
        <div class="max-w-3xl">
            <label for="documentation-search" class="font-concert-one text-2xl text-dark-green dark:text-mint">Search documentation</label>
            <p class="mt-1 text-sm leading-6 text-black/60 dark:text-white/60">Find framework reference pages for handlers, routes, middleware, modules, database, views, and security.</p>
        </div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
            <input id="documentation-search" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="min-h-12 w-full rounded-md border border-dark-green/15 bg-true-white px-4 py-3 text-base text-black shadow-sm outline-none focus:border-dark-green focus:ring-2 focus:ring-dark-green/20 dark:border-mint/20 dark:bg-true-black dark:text-white dark:focus:border-mint dark:focus:ring-mint/20" placeholder="Search routes, handlers, middleware..." />
            <input type="hidden" name="scope" value="<?= htmlspecialchars($searchScope, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>">
            <button class="min-h-12 rounded-md bg-dark-green px-5 py-3 font-semibold text-true-white shadow-sm transition hover:bg-dark-green/90 focus:outline-none focus:ring-2 focus:ring-dark-green/30 dark:bg-mint dark:text-black dark:hover:bg-mint/90 dark:focus:ring-mint/30 sm:w-auto">Search</button>
        </div>

        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
            <?php foreach ($suggestions as $suggestion): ?>
                <a href="/documentation/search?q=<?= urlencode($suggestion) ?>" class="rounded-full border border-dark-green/15 px-2.5 py-1 text-black/55 hover:border-dark-green/30 hover:text-dark-green dark:border-mint/20 dark:text-white/55 dark:hover:border-mint/40 dark:hover:text-mint"><?= htmlspecialchars($suggestion, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></a>
            <?php endforeach; ?>
        </div>
    </form>
    <?php
};
?>

<section class="min-h-[calc(100vh-14rem)] px-4 py-8 font-poppins sm:px-6 lg:px-8">
    <div class="sticky top-0 md:top-16 z-40 -mx-4 mb-6 border-y border-dark-green/10 bg-white/95 px-4 py-3 backdrop-blur dark:border-mint/15 dark:bg-black/90 lg:hidden">
        <button type="button" class="flex w-full items-center justify-between gap-4 rounded-md border border-dark-green/15 bg-true-white px-4 py-3 text-left shadow-sm dark:border-mint/20 dark:bg-true-black" data-mobile-nav-open="documentation-mobile-nav" aria-controls="documentation-mobile-nav" aria-expanded="false">
            <span>
                <span class="block text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45">Browse documentation</span>
                <span class="mt-1 block font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($article?->title ?? 'Documentation home', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
            </span>
            <span aria-hidden="true" class="text-2xl leading-none text-dark-green dark:text-mint">+</span>
        </button>
    </div>

    <div id="documentation-mobile-nav" class="fixed inset-0 z-50 hidden lg:hidden" data-mobile-nav-drawer aria-hidden="true">
        <button type="button" class="absolute inset-0 bg-black/40" data-mobile-nav-close aria-label="Close documentation navigation"></button>
        <div class="y-slider relative h-full w-11/12 max-w-sm overflow-y-auto bg-white px-5 py-5 shadow-xl dark:bg-black">
            <div class="flex items-start justify-between gap-4 border-b border-dark-green/10 pb-5 dark:border-mint/15">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45">Navigation</p>
                    <a href="/documentation" class="mt-1 block font-concert-one text-3xl text-dark-green dark:text-mint">Documentation</a>
                </div>
                <button type="button" class="rounded-md border border-dark-green/15 px-3 py-2 text-sm font-semibold text-dark-green dark:border-mint/20 dark:text-mint" data-mobile-nav-close>Close</button>
            </div>

            <nav class="mt-6 space-y-6 text-sm">
                <?php foreach ($groups as $groupTitle => $groupPages): ?>
                    <div>
                        <p class="px-2 text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45"><?= htmlspecialchars($groupTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></p>
                        <div class="mt-2 space-y-0.5">
                            <?php foreach ($groupPages as $navPage): ?>
                                <a href="/documentation/<?= htmlspecialchars($navPage->slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="block border-l-2 px-3 py-2 <?= $activeSlug === $navPage->slug ? 'border-dark-green bg-dark-green/5 font-semibold text-dark-green dark:border-mint dark:bg-mint/10 dark:text-mint' : 'border-transparent text-black/70 hover:border-dark-green/30 hover:text-dark-green dark:text-white/70 dark:hover:border-mint/30 dark:hover:text-mint' ?>">
                                    <?= htmlspecialchars($navPage->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <div class="grid gap-10 lg:grid-cols-[17rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)_14rem]">
        <aside id="documentation-sidebar" class="y-slider hidden overflow-y-auto border-b border-dark-green/10 pb-5 lg:sticky lg:top-24 lg:block lg:max-h-[calc(100vh-8rem)] lg:border-b-0 lg:pb-0 dark:border-mint/15" data-scroll-memory="documentation-sidebar">
            <div class="border-b border-dark-green/10 pb-5 dark:border-mint/15">
                <a href="/documentation" class="font-concert-one text-3xl text-dark-green dark:text-mint">Documentation</a>
                <p class="mt-2 text-sm leading-6 text-black/60 dark:text-white/60">Focused reference pages for the framework pieces developers reach for most.</p>
            </div>

            <nav class="mt-7 space-y-6 text-sm">
                <?php foreach ($groups as $groupTitle => $groupPages): ?>
                    <div>
                        <p class="px-2 text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45"><?= htmlspecialchars($groupTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></p>
                        <div class="mt-2 space-y-0.5">
                            <?php foreach ($groupPages as $navPage): ?>
                                <a href="/documentation/<?= htmlspecialchars($navPage->slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="block border-l-2 px-3 py-1.5 <?= $activeSlug === $navPage->slug ? 'border-dark-green text-dark-green dark:border-mint dark:text-mint' : 'border-transparent text-black/70 hover:border-dark-green/30 hover:text-dark-green dark:text-white/70 dark:hover:border-mint/30 dark:hover:text-mint' ?>">
                                    <?= htmlspecialchars($navPage->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="min-w-0">
            <?php $renderDocumentationSearch($query, $searchScope, $searchSuggestions); ?>

            <?php if ($mode === 'index'): ?>
                <div class="border-b border-dark-green/10 pb-10 dark:border-mint/15">
                    <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">CorianderPHP</p>
                    <h1 class="mt-3 max-w-3xl font-concert-one text-4xl leading-tight text-dark-green dark:text-mint sm:text-5xl md:text-6xl">Find answers fast, then see them in a real app.</h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-black/70 dark:text-white/70">
                        Use the framework reference for focused lookup: routes, handlers, middleware, views, database, modules, security, cache, and frontend tooling.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="/guided-projects" class="rounded-md border border-dark-green/20 bg-true-white px-4 py-2 font-semibold text-dark-green shadow-sm dark:border-mint/25 dark:bg-true-black dark:text-mint">Guided projects</a>
                    </div>
                </div>

                <div class="mt-10 space-y-10">
                    <?php foreach ($groups as $groupTitle => $groupPages): ?>
                        <section>
                            <div class="flex items-end justify-between gap-4 border-b border-dark-green/10 pb-3 dark:border-mint/15">
                                <h2 class="font-concert-one text-3xl text-dark-green dark:text-mint"><?= htmlspecialchars($groupTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></h2>
                                <?php if ($groupTitle === 'Project: Forum'): ?>
                                    <a href="/documentation/projects/forum" class="text-sm font-semibold text-dark-green dark:text-mint">Start guide</a>
                                <?php endif; ?>
                            </div>
                            <div class="divide-y divide-dark-green/10 dark:divide-mint/10">
                                <?php foreach ($groupPages as $docPage): ?>
                                    <a href="/documentation/<?= htmlspecialchars($docPage->slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="group grid gap-2 px-3 py-6 transition hover:bg-black/5 md:grid-cols-[14rem_1fr]">
                                        <span class="font-semibold text-link-blue underline decoration-link-blue/35 decoration-2 underline-offset-4 group-hover:decoration-link-blue dark:text-link-blue-dark dark:decoration-link-blue-dark/45 dark:group-hover:decoration-link-blue-dark"><?= htmlspecialchars($docPage->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                                        <span class="text-sm leading-6 text-black/65 dark:text-white/65"><?= htmlspecialchars($docPage->description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($mode === 'search'): ?>
                <div class="border-b border-dark-green/10 pb-8 dark:border-mint/15">
                    <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">Reference search</p>
                    <h1 class="mt-3 font-concert-one text-4xl text-dark-green dark:text-mint md:text-5xl">Results for "<?= htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>"</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-black/55 dark:text-white/55">Reference results link to the matching section. Follow its prerequisites before using a code extract.</p>
                </div>

                <div id="documentation-search-results" class="mt-8 space-y-8">
                    <?php if ($quickAnswer !== null): ?>
                        <section class="border-y border-dark-green/10 py-6 dark:border-mint/15">
                            <p class="text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45">From the matching guide</p>
                            <h2 class="mt-2 font-concert-one text-3xl text-dark-green dark:text-mint"><?= htmlspecialchars($quickAnswer['heading'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></h2>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-black/65 dark:text-white/65"><?= htmlspecialchars($quickAnswer['excerpt'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></p>
                            <pre class="mt-4 overflow-x-auto rounded-lg border border-dark-green/15 bg-true-white p-4 text-sm text-black shadow-sm dark:border-mint/20 dark:bg-true-black dark:text-white" data-language="<?= htmlspecialchars($quickAnswer['snippet']['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>"><code code-lang="<?= htmlspecialchars($quickAnswer['snippet']['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>"><?= htmlspecialchars($quickAnswer['snippet']['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></code></pre>
                            <div class="mt-4 flex flex-wrap gap-2 text-sm font-semibold">
                                <a href="/documentation/<?= htmlspecialchars($quickAnswer['page']->slug . '#' . $quickAnswer['anchor'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="rounded-md border border-dark-green/15 px-3 py-2 text-link-blue underline decoration-link-blue/35 decoration-2 underline-offset-4 hover:border-dark-green/30 hover:decoration-link-blue dark:border-mint/20 dark:text-link-blue-dark dark:decoration-link-blue-dark/45 dark:hover:border-mint/40 dark:hover:decoration-link-blue-dark">Read the explanation and prerequisites</a>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if ($query === ''): ?>
                        <p class="py-5 text-black/70 dark:text-white/70">Type a search term to find controllers, migrations, middleware, modules, views, and security notes.</p>
                    <?php elseif ($results === []): ?>
                        <div class="border-y border-dark-green/10 py-6 dark:border-mint/15">
                            <h2 class="font-concert-one text-3xl text-dark-green dark:text-mint">No direct match</h2>
                            <p class="mt-2 text-sm leading-6 text-black/65 dark:text-white/65">Try one of the focused searches below, or use a framework word like controller, route, middleware, migration, module, view, database, or security.</p>
                            <div class="mt-4 flex flex-wrap gap-2 text-sm font-semibold">
                                <?php foreach ($searchSuggestions as $suggestion): ?>
                                    <a href="/documentation/search?q=<?= urlencode($suggestion) ?>" class="rounded-md border border-dark-green/15 px-3 py-2 text-link-blue underline decoration-link-blue/35 decoration-2 underline-offset-4 hover:border-dark-green/30 hover:decoration-link-blue dark:border-mint/20 dark:text-link-blue-dark dark:decoration-link-blue-dark/45 dark:hover:border-mint/40 dark:hover:decoration-link-blue-dark"><?= htmlspecialchars($suggestion, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-dark-green/10 dark:divide-mint/10">
                            <?php foreach ($results as $result): ?>
                                <a href="/documentation/<?= htmlspecialchars($result['page']->slug . '#' . $result['anchor'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="group block px-3 py-6 transition hover:bg-black/5">
                                    <h2 class="font-concert-one text-2xl text-link-blue underline decoration-link-blue/35 decoration-2 underline-offset-4 group-hover:decoration-link-blue dark:text-link-blue-dark dark:decoration-link-blue-dark/45 dark:group-hover:decoration-link-blue-dark"><?= htmlspecialchars($result['page']->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></h2>
                                    <p class="mt-2 text-xs font-semibold text-black/50 dark:text-white/50"><?= htmlspecialchars($result['heading'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></p>
                                    <p class="mt-2 text-sm leading-6 text-black/65 dark:text-white/65"><?= htmlspecialchars($result['excerpt'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif ($article !== null): ?>
                <article class="max-w-4xl">
                    <nav class="mb-6 text-sm text-black/50 dark:text-white/50">
                        <a href="/documentation" class="hover:text-dark-green dark:hover:text-mint">Documentation</a>
                        <span class="mx-2">/</span>
                        <span><?= htmlspecialchars($article->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                    </nav>
                    <?= $article->html ?>
                </article>

                <?php if (str_starts_with($article->slug, 'projects/forum')): ?>
                    <div class="mt-12 border-t border-dark-green/10 pt-6 dark:border-mint/15">
                        <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">Live project</p>
                        <h2 class="mt-2 font-concert-one text-3xl text-dark-green dark:text-mint">See this guide running</h2>
                        <p class="mt-2 max-w-3xl text-black/70 dark:text-white/70">The guided forum project uses a real SQLite database locally. The public live demo is protected so visitor-written content is not stored on this documentation site.</p>
                        <a href="/forum-demo" class="mt-4 inline-flex rounded-md bg-dark-green px-4 py-2 font-semibold text-true-white dark:bg-mint dark:text-black">Open the forum demo</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <aside class="hidden xl:block">
            <?php if ($article !== null && $article->headings !== []): ?>
                <div class="y-slider sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto border-l border-dark-green/10 pl-4 pr-2 dark:border-mint/15" data-scroll-memory="documentation-page-nav">
                    <p class="text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45">On this page</p>
                    <nav class="mt-3 space-y-2 text-sm">
                        <?php foreach ($article->headings as $heading): ?>
                            <?php if ($heading['level'] > 3) continue; ?>
                            <a href="#<?= htmlspecialchars($heading['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" class="block <?= $heading['level'] === 1 ? '' : 'pl-3' ?> text-black/60 hover:text-dark-green dark:text-white/60 dark:hover:text-mint">
                                <?= htmlspecialchars($heading['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
