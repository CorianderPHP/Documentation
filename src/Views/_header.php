<?php
$requestedView = $requestedView ?? 'home';
?>

<!DOCTYPE html>
<html lang="<?= isset($lang) ? $lang : 'en' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'CorianderPHP' ?> - CorianderPHP</title>
    <meta name="description" content="<?= $description ?? 'CorianderPHP documentation.' ?>">

    <link rel="stylesheet" href="<?= \CorianderCore\Core\Support\PublicUrl::versionedAsset('assets/css/output.css') ?>">
    <script defer src="https://analytics.corianderphp.com/script.js" data-website-id="6217b3ee-0db5-4cbe-a2ac-466d90c6c5c1"></script>
</head>

<body class="flex min-h-screen w-full flex-col bg-white text-black scrollbar dark:bg-black dark:text-white">
    <header class="fixed bottom-0 z-50 flex w-full flex-col-reverse font-concert-one pointer-events-none md:sticky md:top-0 md:bottom-auto md:flex-col">
        <div class="w-full text-sm sm:text-lg md:text-2xl border-t border-dark-green/15 bg-true-white/95 shadow-sm backdrop-blur dark:border-mint/15 dark:bg-black/90 md:border-b md:border-t-0">
            <nav class="relative mx-auto flex h-16 w-full max-w-screen-2xl justify-center overflow-x-auto px-4 pointer-events-auto y-slider sm:px-6 md:h-16 md:overflow-visible lg:px-8">
                <div class="flex min-w-max items-center justify-center gap-5 text-sm sm:gap-6 sm:text-base sm:tracking-1 md:gap-10 md:text-2xl">
                    <div class="flex w-auto justify-center gap-5 sm:gap-6 md:gap-10">
                        <a href="/home" title="Go to the Home Page" class="relative flex h-16 items-center whitespace-nowrap after:absolute after:content-[''] after:bottom-3 after:h-[2px] md:after:bottom-3 md:after:h-[3px] after:inset-x-0 after:mx-auto after:bg-dark-green dark:after:bg-mint <?= $requestedView === 'home' ? "after:w-full" : "after:w-0 hover:after:w-full after:transition-['width']" ?>">Home</a>
                        <a href="/documentation" title="Read the documentation" class="relative flex h-16 items-center whitespace-nowrap after:absolute after:content-[''] after:bottom-3 after:h-[2px] md:after:bottom-3 md:after:h-[3px] after:inset-x-0 after:mx-auto after:bg-dark-green dark:after:bg-mint <?= str_starts_with($requestedView, 'documentation') ? "after:w-full" : "after:w-0 hover:after:w-full after:transition-['width']" ?>">Documentation</a>
                        <a href="/guided-projects" title="Explore guided CorianderPHP projects" class="relative flex h-16 items-center whitespace-nowrap after:absolute after:content-[''] after:bottom-3 after:h-[2px] md:after:bottom-3 md:after:h-[3px] after:inset-x-0 after:mx-auto after:bg-dark-green dark:after:bg-mint <?= str_starts_with($requestedView, 'examples') ? "after:w-full" : "after:w-0 hover:after:w-full after:transition-['width']" ?>"><span class="sm:hidden">Guides</span><span class="hidden sm:inline">Guided Projects</span></a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="relative mx-auto w-full max-w-screen-2xl flex-1 pb-20 md:pb-0">
