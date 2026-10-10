<?php
declare(strict_types=1);

if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}

/**
 * @return array<string,array{output:string,zip:string,copy:array<int,array{from:string,to:string}>,write?:array<string,string>}>
 */
function downloadManifests(): array
{
    return [
        'forum-completed' => [
            'output' => 'public/downloads/forum-completed',
            'zip' => 'public/downloads/forum-completed.zip',
            'copy' => [
                ['from' => 'resources/downloads/forum-completed', 'to' => '.'],
                ['from' => 'src/Routes/forum-demo', 'to' => 'src/Routes/forum-demo'],
                ['from' => 'src/Routes/api/forum-demo', 'to' => 'src/Routes/api/forum-demo'],
                ['from' => 'src/Actions/ForumActions.php', 'to' => 'src/Actions/ForumActions.php'],
                ['from' => 'src/Actions/ForumApiActions.php', 'to' => 'src/Actions/ForumApiActions.php'],
                ['from' => 'src/Middleware/ForumDemoAdminMiddleware.php', 'to' => 'src/Middleware/ForumDemoAdminMiddleware.php'],
                ['from' => 'src/Middleware/NumericIdMiddleware.php', 'to' => 'src/Middleware/NumericIdMiddleware.php'],
                ['from' => 'src/Modules/ForumDemo', 'to' => 'src/Modules/ForumDemo'],
                ['from' => 'src/Views/forum-demo.php', 'to' => 'src/Views/forum-demo.php'],
                ['from' => 'src/Views/forum-demo', 'to' => 'src/Views/forum-demo'],
                ['from' => 'nodejs/src/forum-demo', 'to' => 'nodejs/src/forum-demo'],
                ['from' => 'nodejs/tailwind.config.js', 'to' => 'nodejs/tailwind.config.js'],
                ['from' => 'public/assets/css/input.css', 'to' => 'public/assets/css/input.css'],
                ['from' => 'public/assets/css/output.css', 'to' => 'public/assets/css/output.css'],
                ['from' => 'public/assets/fonts', 'to' => 'public/assets/fonts'],
                ['from' => 'public/assets/js/forum-demo', 'to' => 'public/assets/js/forum-demo'],
                ['from' => 'documentation/projects/forum', 'to' => 'documentation/projects/forum'],
            ],
        ],
        'shelter-api-completed' => [
            'output' => 'public/downloads/shelter-api-completed',
            'zip' => 'public/downloads/shelter-api-completed.zip',
            'copy' => [
                ['from' => 'resources/downloads/shelter-api-completed', 'to' => '.'],
            ],
        ],
    ];
}

/**
 * @return string[]
 */
function deprecatedDownloadArtifacts(): array
{
    return [
        'public/downloads/forum-starter',
        'public/downloads/forum-starter.zip',
    ];
}

function absolutePath(string $path): string
{
    return PROJECT_ROOT . '/' . trim(str_replace('\\', '/', $path), '/');
}

function ensureInsideProject(string $path): void
{
    $normalizedProject = str_replace('\\', '/', PROJECT_ROOT);
    $normalizedPath = str_replace('\\', '/', $path);

    if ($normalizedPath !== $normalizedProject && !str_starts_with($normalizedPath, $normalizedProject . '/')) {
        throw new RuntimeException('Refusing to operate outside PROJECT_ROOT: ' . $path);
    }
}

function deletePath(string $path): void
{
    ensureInsideProject($path);

    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        unlink($path);
        return;
    }

    $items = scandir($path);
    if ($items === false) {
        throw new RuntimeException('Unable to scan directory: ' . $path);
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        deletePath($path . '/' . $item);
    }

    rmdir($path);
}

function copyPath(string $source, string $destination): void
{
    ensureInsideProject($source);
    ensureInsideProject($destination);

    if (!file_exists($source)) {
        throw new RuntimeException('Download source does not exist: ' . $source);
    }

    if (is_file($source)) {
        $directory = dirname($destination);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create directory: ' . $directory);
        }

        copy($source, $destination);
        return;
    }

    if (!is_dir($destination) && !mkdir($destination, 0777, true) && !is_dir($destination)) {
        throw new RuntimeException('Unable to create directory: ' . $destination);
    }

    $items = scandir($source);
    if ($items === false) {
        throw new RuntimeException('Unable to scan directory: ' . $source);
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        copyPath($source . '/' . $item, $destination . '/' . $item);
    }
}

function writePackageFiles(string $output, array $files): void
{
    foreach ($files as $relative => $contents) {
        $target = $output . '/' . trim(str_replace('\\', '/', $relative), '/');
        ensureInsideProject($target);
        $directory = dirname($target);

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create directory: ' . $directory);
        }

        file_put_contents($target, rtrim($contents) . "\n");
    }
}

function zipDirectory(string $source, string $zipPath): void
{
    ensureInsideProject($source);
    ensureInsideProject($zipPath);

    if (!class_exists(ZipArchive::class)) {
        throw new RuntimeException('ZipArchive extension is required to generate downloads.');
    }

    $zipDirectory = dirname($zipPath);
    if (!is_dir($zipDirectory) && !mkdir($zipDirectory, 0777, true) && !is_dir($zipDirectory)) {
        throw new RuntimeException('Unable to create directory: ' . $zipDirectory);
    }

    if (is_file($zipPath)) {
        unlink($zipPath);
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
        throw new RuntimeException('Unable to create zip: ' . $zipPath);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $path = $item->getPathname();
        $relative = str_replace('\\', '/', substr($path, strlen($source) + 1));

        if ($item->isDir()) {
            $zip->addEmptyDir($relative);
            continue;
        }

        $zip->addFile($path, $relative);
    }

    $zip->close();
}

foreach (deprecatedDownloadArtifacts() as $artifact) {
    deletePath(absolutePath($artifact));
}

foreach (downloadManifests() as $name => $manifest) {
    $output = absolutePath($manifest['output']);
    deletePath($output);

    foreach ($manifest['copy'] as $copy) {
        $destination = trim($copy['to'], '/') === '.'
            ? $output
            : $output . '/' . trim(str_replace('\\', '/', $copy['to']), '/');
        copyPath(absolutePath($copy['from']), $destination);
    }

    writePackageFiles($output, $manifest['write'] ?? []);
    zipDirectory($output, absolutePath($manifest['zip']));

    echo 'Generated ' . $name . PHP_EOL;
}
