<?php
use CorianderCore\Core\Image\ImageHandler;
?>

<section class="px-4 py-10 font-poppins sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <div class="flex items-center gap-4">
            <?= ImageHandler::render('/public/assets/img/home/coriander_logo.png', 'CorianderPHP logo', 'h-14 w-14 shrink-0 rounded-lg border border-dark-green/10 bg-true-white p-2 shadow-sm ring-4 ring-dark-green/5 dark:border-mint/20 dark:bg-true-black dark:ring-mint/10', 'h-full w-full object-contain', 60) ?>
            <p class="rounded-full border border-dark-green/15 bg-dark-green/5 px-3 py-1 text-sm font-semibold uppercase tracking-1 text-dark-green dark:border-mint/20 dark:bg-mint/10 dark:text-mint">CorianderPHP</p>
        </div>

        <h1 class="mt-5 max-w-4xl font-concert-one text-4xl leading-tight text-dark-green dark:text-mint sm:text-5xl md:text-7xl">
            Documentation
        </h1>
        <p class="mt-5 max-w-2xl text-lg leading-8 text-black/75 dark:text-white/75">
            Choose the path that matches what you need now: search framework behavior in the documentation or build a complete guided project.
        </p>

        <div class="mt-9 flex flex-wrap gap-3">
            <a href="/documentation" class="rounded-md bg-dark-green px-5 py-3 font-semibold text-true-white shadow-sm transition hover:bg-dark-green/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-dark-green dark:bg-mint dark:text-black dark:hover:bg-mint/90 dark:focus-visible:outline-mint">Open documentation</a>
            <a href="/guided-projects" class="rounded-md border border-dark-green/25 bg-true-white px-5 py-3 font-semibold text-dark-green shadow-sm transition hover:border-dark-green focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-dark-green dark:border-mint/30 dark:bg-true-black dark:text-mint dark:hover:border-mint dark:focus-visible:outline-mint">Guided projects</a>
        </div>

        <div class="mt-12 border-t border-dark-green/10 py-5 dark:border-mint/15">
            <p class="text-sm font-semibold uppercase tracking-1 text-dark-green dark:text-mint">Project links</p>
            <div class="mt-4 grid divide-y divide-dark-green/10 dark:divide-mint/10 md:grid-cols-2 md:divide-x md:divide-y-0">
                <a href="https://github.com/CorianderPHP/CorianderPHP" target="_blank" rel="noopener noreferrer" class="group px-3 py-5 transition hover:bg-black/5 md:px-5">
                    <span class="block font-semibold text-black group-hover:text-dark-green dark:text-white dark:group-hover:text-mint">Framework source</span>
                    <span class="mt-1 block text-sm leading-6 text-black/65 dark:text-white/65">Read the CorianderPHP framework code, releases, and roadmap work.</span>
                </a>
                <a href="https://github.com/CorianderPHP/Documentation" target="_blank" rel="noopener noreferrer" class="group px-3 py-5 transition hover:bg-black/5 md:px-5">
                    <span class="block font-semibold text-black group-hover:text-dark-green dark:text-white dark:group-hover:text-mint">Documentation source</span>
                    <span class="mt-1 block text-sm leading-6 text-black/65 dark:text-white/65">See how this website, guided projects, and downloadable examples are maintained.</span>
                </a>
            </div>
            <div class="grid divide-y divide-dark-green/10 border-t border-dark-green/10 dark:divide-mint/10 dark:border-mint/10 md:grid-cols-2 md:divide-x md:divide-y-0">
                <a href="https://github.com/CorianderPHP/CorianderPHP/issues/new" target="_blank" rel="noopener noreferrer" class="group px-3 py-5 transition hover:bg-black/5 md:px-5">
                    <span class="block font-semibold text-black group-hover:text-dark-green dark:text-white dark:group-hover:text-mint">Report a framework bug</span>
                    <span class="mt-1 block text-sm leading-6 text-black/65 dark:text-white/65">Use this when the framework command, router, database layer, middleware, or core behavior is wrong.</span>
                </a>
                <a href="https://github.com/CorianderPHP/Documentation/issues/new" target="_blank" rel="noopener noreferrer" class="group px-3 py-5 transition hover:bg-black/5 md:px-5">
                    <span class="block font-semibold text-black group-hover:text-dark-green dark:text-white dark:group-hover:text-mint">Report a documentation issue</span>
                    <span class="mt-1 block text-sm leading-6 text-black/65 dark:text-white/65">Use this for unclear pages, broken links, outdated examples, or guided project mistakes.</span>
                </a>
            </div>
        </div>
    </div>
</section>
