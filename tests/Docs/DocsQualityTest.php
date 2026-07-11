<?php
declare(strict_types=1);

namespace Tests\Docs;

use Modules\Docs\GuidedProjectRegistry;
use PHPUnit\Framework\TestCase;

final class DocsQualityTest extends TestCase
{
    /**
     * @var string[]
     */
    private const SUPPORTED_CODE_LANGUAGES = [
        'bash',
        'env',
        'html',
        'http',
        'js',
        'javascript',
        'json',
        'php',
        'powershell',
        'sh',
        'shell',
        'sql',
        'structure',
        'text',
        'ts',
        'tsx',
        'txt',
        'typescript',
    ];

    public function testGuidedProjectNavigationTargetsExistingMarkdown(): void
    {
        foreach ((new GuidedProjectRegistry())->all() as $project) {
            foreach ($project->groups as $items) {
                foreach ($items as $item) {
                    $path = PROJECT_ROOT . '/docs/' . $item['slug'] . '.md';
                    self::assertFileExists($path, $item['slug']);
                }
            }
        }
    }

    public function testDownloadFilesDeclaredByGuidedProjectsExist(): void
    {
        foreach ((new GuidedProjectRegistry())->all() as $project) {
            self::assertFileExists(PROJECT_ROOT . $project->downloadPath, $project->downloadPath);
            self::assertGreaterThan(1024, filesize(PROJECT_ROOT . $project->downloadPath));
        }
    }

    public function testCodeFenceLanguagesAreSupportedByHighlighter(): void
    {
        foreach ($this->markdownFiles() as $file) {
            preg_match_all('/^```([A-Za-z0-9_-]*)/m', (string) file_get_contents($file), $matches);
            foreach ($matches[1] as $language) {
                if ($language === '') {
                    continue;
                }

                self::assertContains(strtolower($language), self::SUPPORTED_CODE_LANGUAGES, $file . ' uses unsupported code fence language: ' . $language);
            }
        }
    }

    public function testInternalMarkdownLinksResolve(): void
    {
        foreach ($this->markdownFiles() as $file) {
            preg_match_all('/\[[^\]]+\]\(([^)]+)\)/', (string) file_get_contents($file), $matches);
            foreach ($matches[1] as $target) {
                $path = strtok($target, '#?');
                if (!is_string($path) || $path === '' || preg_match('/^[a-z]+:/i', $path) === 1) {
                    continue;
                }

                if (str_starts_with($path, '/public/downloads/')) {
                    self::assertFileExists(PROJECT_ROOT . $path, $file . ' links to missing download ' . $path);
                    continue;
                }

                if (str_starts_with($path, '/docs/')) {
                    $slug = substr($path, strlen('/docs/'));
                    self::assertTrue($slug === '' || $this->markdownTargetExists($slug), $file . ' links to missing docs page ' . $path);
                    continue;
                }

                if (str_starts_with($path, '/guided-projects/')) {
                    self::assertTrue($this->guidedProjectPathExists($path), $file . ' links to missing guided project page ' . $path);
                }
            }
        }
    }

    public function testNoAppSpecificCodeLivesUnderCorianderCore(): void
    {
        $forbidden = ['ForumDemo', 'ShelterPlayground', 'ShelterApi', 'DocumentationRepository'];

        foreach ($this->files(PROJECT_ROOT . '/CorianderCore') as $file) {
            $contents = (string) file_get_contents($file);
            foreach ($forbidden as $needle) {
                self::assertStringNotContainsString($needle, $contents, 'App-specific code found in core file ' . $file);
            }
        }
    }

    public function testOldExamplesLinksOnlyRemainInRedirectRoutes(): void
    {
        $scanRoots = [
            PROJECT_ROOT . '/docs',
            PROJECT_ROOT . '/public/public_views',
        ];

        foreach ($scanRoots as $root) {
            foreach ($this->files($root) as $file) {
                self::assertStringNotContainsString('href="/examples', (string) file_get_contents($file), 'Old /examples link found in ' . $file);
                self::assertStringNotContainsString('](/examples', (string) file_get_contents($file), 'Old /examples markdown link found in ' . $file);
            }
        }
    }

    /**
     * @return string[]
     */
    private function markdownFiles(): array
    {
        return array_values(array_filter($this->files(PROJECT_ROOT . '/docs'), static fn(string $file): bool => str_ends_with($file, '.md')));
    }

    /**
     * @return string[]
     */
    private function files(string $root): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }

    private function markdownTargetExists(string $slug): bool
    {
        return is_file(PROJECT_ROOT . '/docs/' . trim($slug, '/') . '.md')
            || is_file(PROJECT_ROOT . '/docs/' . trim($slug, '/') . '/index.md');
    }

    private function guidedProjectPathExists(string $path): bool
    {
        foreach ((new GuidedProjectRegistry())->all() as $project) {
            if ($path === $project->basePath) {
                return $this->markdownTargetExists($project->indexSlug());
            }

            if (str_starts_with($path, $project->basePath . '/')) {
                return $this->markdownTargetExists($project->baseSlug . '/' . substr($path, strlen($project->basePath . '/')));
            }
        }

        return false;
    }
}
