<?php
use CorianderCore\Core\Image\ImageHandler;
?>

<section class="px-4 py-10 font-poppins sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <div class="flex items-center gap-4">
            <?= ImageHandler::render('/public/assets/img/home/coriander_logo.png', 'CorianderPHP logo', 'h-14 w-14 shrink-0 rounded-lg border border-dark-green/10 bg-true-white p-2 shadow-sm ring-4 ring-dark-green/5 dark:border-peach/20 dark:bg-true-black dark:ring-peach/10', 'h-full w-full object-contain', 60) ?>
            <p class="rounded-full border border-dark-green/15 bg-dark-green/5 px-3 py-1 text-sm font-semibold uppercase tracking-1 text-dark-green dark:border-peach/20 dark:bg-peach/10 dark:text-peach">CorianderPHP</p>
        </div>

        <h1 class="mt-5 max-w-4xl font-concert-one text-4xl leading-tight text-dark-green dark:text-peach sm:text-5xl md:text-7xl">
            Documentation
        </h1>
        <p class="mt-5 max-w-2xl text-lg leading-8 text-black/75 dark:text-white/75">
            Choose the path that matches what you need now: start a clean app, look up framework behavior, or build the forum permissions project.
        </p>

        <div class="mt-9 flex flex-wrap gap-3">
            <a href="/start" class="rounded-md bg-dark-green px-5 py-3 font-semibold text-true-white shadow-sm transition hover:bg-dark-green/90 dark:bg-peach dark:text-black dark:hover:bg-peach/90">Start a project</a>
            <a href="/docs" class="rounded-md border border-dark-green/25 bg-true-white px-5 py-3 font-semibold text-dark-green shadow-sm transition hover:border-dark-green dark:border-peach/30 dark:bg-true-black dark:text-peach dark:hover:border-peach">Open documentation</a>
        </div>

        <div class="mt-12 divide-y divide-dark-green/10 border-y border-dark-green/10 dark:divide-peach/10 dark:border-peach/15">
            <a href="/start" class="grid gap-2 px-3 py-6 transition hover:bg-black/5 md:grid-cols-[13rem_1fr_auto] md:items-center">
                <span class="font-concert-one text-3xl text-dark-green dark:text-peach">Start</span>
                <span class="text-sm leading-6 text-black/70 dark:text-white/70">Create the first route, controller, view, module, assets, and optional database configuration.</span>
                <span class="text-sm font-semibold text-dark-green dark:text-peach">Begin</span>
            </a>
            <a href="/docs" class="grid gap-2 px-3 py-6 transition hover:bg-black/5 md:grid-cols-[13rem_1fr_auto] md:items-center">
                <span class="font-concert-one text-3xl text-dark-green dark:text-peach">Documentation</span>
                <span class="text-sm leading-6 text-black/70 dark:text-white/70">Search routing, controllers, middleware, views, custom modules, database, security, cache, and frontend tooling.</span>
                <span class="text-sm font-semibold text-dark-green dark:text-peach">Search</span>
            </a>
            <a href="/guided-projects/forum" class="grid gap-2 px-3 py-6 transition hover:bg-black/5 md:grid-cols-[13rem_1fr_auto] md:items-center">
                <span class="font-concert-one text-3xl text-dark-green dark:text-peach">Guided Project</span>
                <span class="text-sm leading-6 text-black/70 dark:text-white/70">Build a forum with SQLite persistence, authentication, permissions, admin middleware, API endpoints, and protected public demo behavior.</span>
                <span class="text-sm font-semibold text-dark-green dark:text-peach">Build</span>
            </a>
        </div>
    </div>
</section>
