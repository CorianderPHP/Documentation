<?php
$requestedView = isset($__corianderRequestedView) ? $__corianderRequestedView : 'home';
$metaDataPath = PROJECT_ROOT . '/public/public_views/' . $requestedView . '/metadata.php';
if (file_exists($metaDataPath)) {
    require_once $metaDataPath;
}
?>

<!DOCTYPE html>
<html lang="<?= isset($lang) ? $lang : 'en' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    if (isset($metadata)) {
        echo $metadata;
    } else {
        echo '<title>No configured title</title>';
        echo '<meta name="description" content="No configured description.">';
    }
    ?>

    <link rel="stylesheet" href="<?= \CorianderCore\Core\Support\PublicUrl::versionedAsset('assets/css/output.css') ?>">
</head>

<body class="bg-white dark:bg-black w-full absolute min-h-full scrollbar text-black dark:text-white">
    <header class="md:w-full w-screen fixed md:sticky md:top-0 h-auto bottom-0 z-50 font-concert-one pointer-events-none flex md:flex-col flex-col-reverse">
        <div class="w-full text-sm sm:text-lg md:text-2xl border-t border-dark-green/15 bg-true-white/95 shadow-sm backdrop-blur dark:border-peach/15 dark:bg-black/90 md:border-b md:border-t-0">
            <nav class="md:max-w-screen-2xl w-full mx-auto relative flex justify-end md:h-16 h-14 pointer-events-auto">
                <div class="flex sm:tracking-1 md:justify-end justify-around w-full">
                    <div class="w-auto flex gap-3 sm:gap-5 md:gap-10">
                        <a href="/home" title="Go to the Home Page" class="relative block m-auto after:absolute after:content-[''] after:-bottom-[2px] md:after:-bottom-1 after:h-[2px] md:after:h-[3px] after:inset-x-0 after:mx-auto after:bg-dark-green dark:after:bg-peach <?= $requestedView === 'home' ? "after:w-full" : "after:w-0 hover:after:w-full after:transition-['width']" ?>">Home</a>
                        <a href="/docs" title="Read the documentation" class="relative block m-auto after:absolute after:content-[''] after:-bottom-[2px] md:after:-bottom-1 after:h-[2px] md:after:h-[3px] after:inset-x-0 after:mx-auto after:bg-dark-green dark:after:bg-peach <?= str_starts_with($requestedView, 'docs') ? "after:w-full" : "after:w-0 hover:after:w-full after:transition-['width']" ?>">Documentation</a>
                        <a href="/start" title="Learn how to start a CorianderPHP project" class="relative block m-auto after:absolute after:content-[''] after:-bottom-[2px] md:after:-bottom-1 after:h-[2px] md:after:h-[3px] after:inset-x-0 after:mx-auto after:bg-dark-green dark:after:bg-peach <?= str_starts_with($requestedView, 'start') ? "after:w-full" : "after:w-0 hover:after:w-full after:transition-['width']" ?>">Start</a>
                        <a href="/guided-projects" title="Explore guided CorianderPHP projects" class="relative block m-auto after:absolute after:content-[''] after:-bottom-[2px] md:after:-bottom-1 after:h-[2px] md:after:h-[3px] after:inset-x-0 after:mx-auto after:bg-dark-green dark:after:bg-peach <?= str_starts_with($requestedView, 'examples') ? "after:w-full" : "after:w-0 hover:after:w-full after:transition-['width']" ?>">Guided Projects</a>
                        <a href="https://github.com/CorianderPHP/CorianderPHP/issues" target="_blank" rel="noopener noreferrer" title="Report a bug on GitHub" class="relative block m-auto after:absolute after:content-[''] after:-bottom-[2px] md:after:-bottom-1 after:h-[2px] md:after:h-[3px] after:inset-x-0 after:mx-auto after:w-0 after:bg-dark-green hover:after:w-full after:transition-['width'] dark:after:bg-peach"><span class="sm:hidden">Bug</span><span class="hidden sm:inline">Report Bug</span></a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="relative max-w-screen-2xl mx-auto pb-16">
