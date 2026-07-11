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
$searchSuggestions = ['controllers', 'routes', 'middleware', 'migrations', 'modules', 'production', 'debugging'];
$quickAnswers = [
    [
        'keywords' => ['controller', 'controllers'],
        'title' => 'Create a controller',
        'summary' => 'Use the controller generator, then keep request handling in the controller and move business logic into a service or repository.',
        'language' => 'bash',
        'code' => "php coriander make:controller Blog\nphp coriander make:controller Blog --api",
        'links' => [
            ['label' => 'Controller reference', 'href' => '/documentation/controllers'],
            ['label' => 'Routing reference', 'href' => '/documentation/routing'],
        ],
    ],
    [
        'keywords' => ['route', 'routes', 'routing', 'router'],
        'title' => 'Create a route file',
        'summary' => 'Use the route generator for larger feature areas, then require the generated file from public/routes.php.',
        'language' => 'bash',
        'code' => "php coriander make:route admin\nphp coriander make:route admin/users",
        'links' => [
            ['label' => 'Routing reference', 'href' => '/documentation/routing'],
            ['label' => 'Documentation home', 'href' => '/documentation'],
        ],
    ],
    [
        'keywords' => ['middleware', 'auth', 'permission', 'permissions'],
        'title' => 'Add route middleware',
        'summary' => 'Put project middleware in src/Middleware and attach it to a route group or a specific route. Keep CorianderCore middleware framework-owned.',
        'language' => 'php',
        'code' => "use Middleware\\AuthMiddleware;\n\n\$router->group('admin', [new AuthMiddleware()], function (\$router): void {\n    \$router->get('dashboard', fn () => 'Dashboard');\n});",
        'links' => [
            ['label' => 'Middleware reference', 'href' => '/documentation/middleware'],
            ['label' => 'Security reference', 'href' => '/documentation/security'],
        ],
    ],
    [
        'keywords' => ['migration', 'migrations', 'sql', 'database', 'sqlite', 'mysql'],
        'title' => 'Create database changes',
        'summary' => 'Create migrations for schema changes and use sqlScript for custom SQL queries that need to stay readable.',
        'language' => 'bash',
        'code' => "php coriander make:migration create_posts_table\nphp coriander migrate",
        'links' => [
            ['label' => 'Database reference', 'href' => '/documentation/database'],
            ['label' => 'Forum data model guide', 'href' => '/guided-projects/forum/data-model'],
        ],
    ],
    [
        'keywords' => ['module', 'modules'],
        'title' => 'Use a custom module',
        'summary' => 'Put app-specific reusable code in src/Modules. Official framework modules stay in the CorianderPHP project; app modules stay owned by this app.',
        'language' => 'structure',
        'code' => "src/\n  Modules/\n    Blog/\n      BlogRepository.php\n      BlogService.php",
        'links' => [
            ['label' => 'Modules reference', 'href' => '/documentation/modules'],
            ['label' => 'Controller reference', 'href' => '/documentation/controllers'],
        ],
    ],
    [
        'keywords' => ['view', 'views', 'template', 'html'],
        'title' => 'Render a view',
        'summary' => 'Keep page markup in views and pass prepared data from controllers. Use CSRF helpers for state-changing forms.',
        'language' => 'php',
        'code' => "\$this->view->render('blog/show', [\n    'post' => \$post,\n]);",
        'links' => [
            ['label' => 'Views reference', 'href' => '/documentation/views'],
            ['label' => 'Security reference', 'href' => '/documentation/security'],
        ],
    ],
    [
        'keywords' => ['architecture', 'structure', 'organize', 'lifecycle'],
        'title' => 'Choose where code belongs',
        'summary' => 'Use the architecture and lifecycle pages when you need to decide between a controller, middleware, module, repository, or view.',
        'language' => 'structure',
        'code' => "src/\n  Controllers/\n  Middleware/\n  Modules/\n  Routes/\npublic/\n  public_views/\ndocumentation/",
        'links' => [
            ['label' => 'Architecture guide', 'href' => '/documentation/app-architecture'],
            ['label' => 'Request lifecycle', 'href' => '/documentation/request-lifecycle'],
        ],
    ],
    [
        'keywords' => ['production', 'deploy', 'plesk', 'hosting'],
        'title' => 'Prepare for production',
        'summary' => 'Before deploying, check environment values, document root, HTTPS/proxies, migrations, writable paths, logs, and generated frontend assets.',
        'language' => 'bash',
        'code' => "composer dump-autoload\ncomposer generate-downloads\ncomposer test\nphp coriander nodejs run build-prod",
        'links' => [
            ['label' => 'Production checklist', 'href' => '/documentation/production'],
            ['label' => 'Debugging guide', 'href' => '/documentation/debugging'],
        ],
    ],
    [
        'keywords' => ['test', 'testing', 'tests'],
        'title' => 'Test app-owned behavior',
        'summary' => 'Start with modules, permissions, repositories, route smoke tests, and documentation quality checks before adding heavier browser tests.',
        'language' => 'bash',
        'code' => "composer test\nphp coriander nodejs run build-prod",
        'links' => [
            ['label' => 'Testing an app', 'href' => '/documentation/testing'],
            ['label' => 'Upgrade guide', 'href' => '/documentation/upgrades'],
        ],
    ],
];
$normalizedQuery = strtolower(trim($query));
$quickAnswer = null;
if ($normalizedQuery !== '') {
    foreach ($quickAnswers as $answer) {
        foreach ($answer['keywords'] as $keyword) {
            if (str_contains($normalizedQuery, $keyword)) {
                $quickAnswer = $answer;
                break 2;
            }
        }
    }
}
$renderDocumentationSearch = static function (string $query, string $searchScope, array $suggestions): void {
    ?>
    <form action="/documentation/search" method="GET" class="mb-8 border-b border-dark-green/10 pb-6 dark:border-mint/15">
        <div class="max-w-3xl">
            <label for="documentation-search" class="font-concert-one text-2xl text-dark-green dark:text-mint">Search documentation</label>
            <p class="mt-1 text-sm leading-6 text-black/60 dark:text-white/60">Find framework reference pages for controllers, routes, middleware, modules, database, views, and security.</p>
        </div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
            <input id="documentation-search" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="min-h-12 w-full rounded-md border border-dark-green/15 bg-true-white px-4 py-3 text-base text-black shadow-sm outline-none focus:border-dark-green focus:ring-2 focus:ring-dark-green/20 dark:border-mint/20 dark:bg-true-black dark:text-white dark:focus:border-mint dark:focus:ring-mint/20" placeholder="Search routes, controllers, middleware..." />
            <input type="hidden" name="scope" value="<?= htmlspecialchars($searchScope, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <button class="min-h-12 rounded-md bg-dark-green px-5 py-3 font-semibold text-true-white shadow-sm transition hover:bg-dark-green/90 focus:outline-none focus:ring-2 focus:ring-dark-green/30 dark:bg-mint dark:text-black dark:hover:bg-mint/90 dark:focus:ring-mint/30 sm:w-auto">Search</button>
        </div>

        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
            <?php foreach ($suggestions as $suggestion): ?>
                <a href="/documentation/search?q=<?= urlencode($suggestion) ?>" class="rounded-full border border-dark-green/15 px-2.5 py-1 text-black/55 hover:border-dark-green/30 hover:text-dark-green dark:border-mint/20 dark:text-white/55 dark:hover:border-mint/40 dark:hover:text-mint"><?= htmlspecialchars($suggestion, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </div>
    </form>
    <?php
};
$resultGroups = [];
foreach ($results as $result) {
    $resultGroups[$result['page']->section] ??= [];
    $resultGroups[$result['page']->section][] = $result;
}
?>

<section class="min-h-[calc(100vh-14rem)] px-4 py-8 font-poppins sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-[17rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)_14rem]">
        <aside id="documentation-sidebar" class="y-slider max-h-80 overflow-y-auto border-b border-dark-green/10 pb-5 lg:sticky lg:top-24 lg:max-h-[calc(100vh-8rem)] lg:border-b-0 lg:pb-0 dark:border-mint/15" data-scroll-memory="documentation-sidebar">
            <div class="border-b border-dark-green/10 pb-5 dark:border-mint/15">
                <a href="/documentation" class="font-concert-one text-3xl text-dark-green dark:text-mint">Documentation</a>
                <p class="mt-2 text-sm leading-6 text-black/60 dark:text-white/60">Focused reference pages for the framework pieces developers reach for most.</p>
            </div>

            <nav class="mt-7 space-y-6 text-sm">
                <?php foreach ($groups as $groupTitle => $groupPages): ?>
                    <div>
                        <p class="px-2 text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45"><?= htmlspecialchars($groupTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <div class="mt-2 space-y-0.5">
                            <?php foreach ($groupPages as $navPage): ?>
                                <a href="/documentation/<?= htmlspecialchars($navPage->slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="block border-l-2 px-3 py-1.5 <?= $activeSlug === $navPage->slug ? 'border-dark-green text-dark-green dark:border-mint dark:text-mint' : 'border-transparent text-black/70 hover:border-dark-green/30 hover:text-dark-green dark:text-white/70 dark:hover:border-mint/30 dark:hover:text-mint' ?>">
                                    <?= htmlspecialchars($navPage->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
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
                        Use the framework reference for focused lookup: routes, controllers, middleware, views, database, modules, security, cache, and frontend tooling.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="/guided-projects" class="rounded-md border border-dark-green/20 bg-true-white px-4 py-2 font-semibold text-dark-green shadow-sm dark:border-mint/25 dark:bg-true-black dark:text-mint">Guided projects</a>
                    </div>
                </div>

                <div class="mt-10 space-y-10">
                    <?php foreach ($groups as $groupTitle => $groupPages): ?>
                        <section>
                            <div class="flex items-end justify-between gap-4 border-b border-dark-green/10 pb-3 dark:border-mint/15">
                                <h2 class="font-concert-one text-3xl text-dark-green dark:text-mint"><?= htmlspecialchars($groupTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                                <?php if ($groupTitle === 'Project: Forum'): ?>
                                    <a href="/documentation/projects/forum" class="text-sm font-semibold text-dark-green dark:text-mint">Start guide</a>
                                <?php endif; ?>
                            </div>
                            <div class="divide-y divide-dark-green/10 dark:divide-mint/10">
                                <?php foreach ($groupPages as $docPage): ?>
                                    <a href="/documentation/<?= htmlspecialchars($docPage->slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="grid gap-2 px-3 py-6 transition hover:bg-black/5 md:grid-cols-[14rem_1fr]">
                                        <span class="font-semibold text-dark-green dark:text-mint"><?= htmlspecialchars($docPage->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                        <span class="text-sm leading-6 text-black/65 dark:text-white/65"><?= htmlspecialchars($docPage->description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($mode === 'search'): ?>
                <div class="border-b border-dark-green/10 pb-8 dark:border-mint/15">
                    <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">Reference search</p>
                    <h1 class="mt-3 font-concert-one text-4xl text-dark-green dark:text-mint md:text-5xl">Results for "<?= htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-black/55 dark:text-white/55">Search results focus on framework reference pages. Open a result when you need the full explanation, or use the quick answer below when it matches what you are trying to build.</p>
                </div>

                <div id="documentation-search-results" class="mt-8 space-y-8">
                    <?php if ($quickAnswer !== null): ?>
                        <section class="border-y border-dark-green/10 py-6 dark:border-mint/15">
                            <p class="text-xs font-semibold uppercase tracking-1 text-black/45 dark:text-white/45">Quick answer</p>
                            <h2 class="mt-2 font-concert-one text-3xl text-dark-green dark:text-mint"><?= htmlspecialchars($quickAnswer['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-black/65 dark:text-white/65"><?= htmlspecialchars($quickAnswer['summary'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                            <pre class="mt-4 overflow-x-auto rounded-lg border border-dark-green/15 bg-true-white p-4 text-sm text-black shadow-sm dark:border-mint/20 dark:bg-true-black dark:text-white" data-language="<?= htmlspecialchars($quickAnswer['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><code code-lang="<?= htmlspecialchars($quickAnswer['language'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($quickAnswer['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code></pre>
                            <div class="mt-4 flex flex-wrap gap-2 text-sm font-semibold">
                                <?php foreach ($quickAnswer['links'] as $link): ?>
                                    <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="rounded-md border border-dark-green/15 px-3 py-2 text-dark-green hover:border-dark-green/30 dark:border-mint/20 dark:text-mint dark:hover:border-mint/40"><?= htmlspecialchars($link['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                                <?php endforeach; ?>
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
                                    <a href="/documentation/search?q=<?= urlencode($suggestion) ?>" class="rounded-md border border-dark-green/15 px-3 py-2 text-dark-green hover:border-dark-green/30 dark:border-mint/20 dark:text-mint dark:hover:border-mint/40"><?= htmlspecialchars($suggestion, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($resultGroups as $section => $sectionResults): ?>
                            <section>
                                <h2 class="border-b border-dark-green/10 pb-3 font-concert-one text-3xl text-dark-green dark:border-mint/15 dark:text-mint"><?= htmlspecialchars($section, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                                <div class="divide-y divide-dark-green/10 dark:divide-mint/10">
                                    <?php foreach ($sectionResults as $result): ?>
                                        <a href="/documentation/<?= htmlspecialchars($result['page']->slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="block px-3 py-6 transition hover:bg-black/5">
                                            <h3 class="font-concert-one text-2xl text-dark-green dark:text-mint"><?= htmlspecialchars($result['page']->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h3>
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
                        <a href="/documentation" class="hover:text-dark-green dark:hover:text-mint">Documentation</a>
                        <span class="mx-2">/</span>
                        <span><?= htmlspecialchars($page->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    </nav>
                    <?= $page->html ?>
                </article>

                <?php if (str_starts_with($page->slug, 'projects/forum')): ?>
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
            <?php if ($page !== null && $page->headings !== []): ?>
                <div class="y-slider sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto border-l border-dark-green/10 pl-4 pr-2 dark:border-mint/15" data-scroll-memory="documentation-page-nav">
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
