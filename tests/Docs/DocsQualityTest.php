<?php
declare(strict_types=1);

namespace Tests\Docs;

use App\Modules\Docs\GuidedProjectRegistry;
use PHPUnit\Framework\TestCase;

final class DocsQualityTest extends TestCase
{
    /**
     * @var string[]
     */
    private const SUPPORTED_CODE_LANGUAGES = [
        'bash',
        'choices',
        'env',
        'flow',
        'html',
        'http',
        'js',
        'javascript',
        'json',
        'php',
        'powershell',
        'responsibilities',
        'sh',
        'shell',
        'sql',
        'structure',
        'text',
        'ts',
        'tsx',
        'txt',
        'typescript',
        'workflow',
    ];

    public function testGuidedProjectNavigationTargetsExistingMarkdown(): void
    {
        foreach ((new GuidedProjectRegistry())->all() as $project) {
            foreach ($project->groups as $items) {
                foreach ($items as $item) {
                    $path = PROJECT_ROOT . '/documentation/' . $item['slug'] . '.md';
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

    public function testDownloadGeneratorIsAvailable(): void
    {
        self::assertFileExists(PROJECT_ROOT . '/scripts/generate-downloads.php');
        self::assertStringContainsString('downloadManifests', (string) file_get_contents(PROJECT_ROOT . '/scripts/generate-downloads.php'));
        self::assertStringContainsString('generate-downloads', (string) file_get_contents(PROJECT_ROOT . '/composer.json'));
    }

    public function testWorkflowsUseFrameworkNodejsBuildCommand(): void
    {
        $workflows = [
            PROJECT_ROOT . '/.github/workflows/tests.yml',
            PROJECT_ROOT . '/.github/workflows/update-framework.yml',
        ];

        foreach ($workflows as $workflow) {
            $contents = (string) file_get_contents($workflow);
            self::assertStringContainsString('php coriander nodejs run build-prod', $contents, $workflow . ' should build assets through the framework CLI.');
            self::assertStringNotContainsString('npm run build-prod', $contents, $workflow . ' should not bypass the framework CLI build wrapper.');
        }
    }

    public function testCodeHighlighterIsOnlyBundledBySharedAsset(): void
    {
        foreach ($this->files(PROJECT_ROOT . '/nodejs/src') as $file) {
            $normalized = str_replace('\\', '/', $file);
            if (str_ends_with($normalized, 'nodejs/src/documentation/CodeHighlighter.ts') || str_ends_with($normalized, 'nodejs/src/documentation-code/index.ts')) {
                continue;
            }

            self::assertStringNotContainsString('import { CodeHighlighter', (string) file_get_contents($file), 'Code highlighter import should stay in the shared documentation-code bundle: ' . $file);
        }
    }

    public function testAnalyticsHostIsAllowedByContentSecurityPolicy(): void
    {
        $header = (string) file_get_contents(PROJECT_ROOT . '/src/Views/_header.php');
        $policy = (string) file_get_contents(PROJECT_ROOT . '/src/Routes/_middleware.php');

        self::assertStringContainsString('https://analytics.corianderphp.com/script.js', $header);
        self::assertStringContainsString("script-src 'self' https://analytics.corianderphp.com", $policy);
        self::assertStringContainsString("connect-src 'self' https://analytics.corianderphp.com", $policy);
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
                $this->assertInternalTargetResolves($file, $target);
            }

            preg_match_all('/^```choices\s*\R([\s\S]*?)^```/m', (string) file_get_contents($file), $choiceMatches);
            foreach ($choiceMatches[1] as $choiceBlock) {
                foreach (preg_split('/\r\n|\r|\n/', trim($choiceBlock)) ?: [] as $choiceLine) {
                    $parts = array_map('trim', explode('|', $choiceLine, 3));
                    if (($parts[2] ?? '') !== '') {
                        $this->assertInternalTargetResolves($file, $parts[2]);
                    }
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
            PROJECT_ROOT . '/documentation',
            PROJECT_ROOT . '/src/Views',
        ];

        foreach ($scanRoots as $root) {
            foreach ($this->files($root) as $file) {
                self::assertStringNotContainsString('href="/examples', (string) file_get_contents($file), 'Old /examples link found in ' . $file);
                self::assertStringNotContainsString('](/examples', (string) file_get_contents($file), 'Old /examples markdown link found in ' . $file);
            }
        }
    }

    public function testInstallationDocumentationContainsRequiredSetupSteps(): void
    {
        $contents = (string) file_get_contents(PROJECT_ROOT . '/documentation/installation.md');

        self::assertStringContainsString('composer install', $contents);
        self::assertStringContainsString('php coriander nodejs run install', $contents);
        self::assertStringContainsString('.github/', $contents);
        self::assertStringContainsString('AGENTS.md', $contents);
        self::assertStringContainsString('readme.md', $contents);
    }

    public function testImageHandlerDocumentationUsesOptionsArrayApi(): void
    {
        $contents = (string) file_get_contents(PROJECT_ROOT . '/documentation/assets.md');

        self::assertStringContainsString("ImageHandler::render('/public/assets/img/logo.png', [", $contents);
        self::assertStringContainsString("'loading' => 'lazy'", $contents);
        self::assertStringContainsString("'decoding' => 'async'", $contents);
    }

    public function testCliDocumentationCoversDetailedHelp(): void
    {
        $contents = (string) file_get_contents(PROJECT_ROOT . '/documentation/cli.md');

        self::assertStringContainsString('php coriander help', $contents);
        self::assertStringContainsString('php coriander help make', $contents);
        self::assertStringContainsString('php coriander help nodejs', $contents);
        self::assertStringContainsString('php coriander help migrate', $contents);
        self::assertStringContainsString('php coriander --help', $contents);
        self::assertStringContainsString('php coriander -h', $contents);
        self::assertStringContainsString('php coriander make --help', $contents);
        self::assertStringContainsString('php coriander nodejs --help', $contents);
    }

    public function testViewsDocumentationIsSplitByViewType(): void
    {
        $overview = (string) file_get_contents(PROJECT_ROOT . '/documentation/views.md');
        $static = (string) file_get_contents(PROJECT_ROOT . '/documentation/static-views.md');
        $dynamic = (string) file_get_contents(PROJECT_ROOT . '/documentation/dynamic-views.md');

        self::assertStringContainsString('Static View Guide](/documentation/static-views)', $overview);
        self::assertStringContainsString('Dynamic View Guide](/documentation/dynamic-views)', $overview);
        self::assertStringContainsString('Assets And Images](/documentation/assets)', $overview);
        self::assertStringContainsString('src/Views/about.php', $static);
        self::assertStringContainsString('src/Routes/about.get.php', $static);
        self::assertStringContainsString("Responses::view('articles/show'", $dynamic);
        self::assertStringContainsString('src/Views/articles/show.php', $dynamic);
        self::assertStringContainsString('src/Routes/articles/[id].get.php', $dynamic);
        self::assertStringContainsString('$request->getAttribute(\'id\')', $dynamic);
    }

    public function testGuidedProjectsExplainRequestLifecycles(): void
    {
        $forumIndex = (string) file_get_contents(PROJECT_ROOT . '/documentation/projects/forum/index.md');
        $forumRoutes = (string) file_get_contents(PROJECT_ROOT . '/documentation/projects/forum/routes.md');
        $shelterIndex = (string) file_get_contents(PROJECT_ROOT . '/documentation/projects/shelter-api/index.md');
        $shelterRoutes = (string) file_get_contents(PROJECT_ROOT . '/documentation/projects/shelter-api/routes.md');

        self::assertStringContainsString('```workflow', $forumIndex);
        self::assertStringContainsString('Route|Maps the URL to a handler action.', $forumIndex);
        self::assertStringContainsString('You are here in the flow', $forumRoutes);
        self::assertStringContainsString('```workflow', $shelterIndex);
        self::assertStringContainsString('Repository|Runs SQL and returns storage data.', $shelterIndex);
        self::assertStringContainsString('Method file|Discovery selects', $shelterRoutes);
    }

    public function testGuidedProjectDownloadSourcesContainLearnerOrientationComments(): void
    {
        $forumController = (string) file_get_contents(PROJECT_ROOT . '/src/Actions/ForumActions.php');
        $forumView = (string) file_get_contents(PROJECT_ROOT . '/src/Views/forum-demo/topic.php');
        $shelterController = (string) file_get_contents(PROJECT_ROOT . '/resources/downloads/shelter-api-completed/src/Actions/ShelterAnimalActions.php');
        $shelterRepository = (string) file_get_contents(PROJECT_ROOT . '/resources/downloads/shelter-api-completed/src/Modules/ShelterApi/AnimalRepository.php');
        $downloadReadme = (string) file_get_contents(PROJECT_ROOT . '/resources/downloads/forum-completed/README.md');

        self::assertStringContainsString('routes call these public methods', $forumController);
        self::assertStringContainsString('Rendered by ForumActions::showTopic()', $forumView);
        self::assertStringContainsString('Route entrypoint for /api/shelter/animals', $shelterController);
        self::assertStringContainsString('Persistence layer', $shelterRepository);
        self::assertStringContainsString('Method file|Maps the URL and HTTP method to an action.', $downloadReadme);
    }

    /**
     * @return string[]
     */
    private function markdownFiles(): array
    {
        return array_values(array_filter($this->files(PROJECT_ROOT . '/documentation'), static fn(string $file): bool => str_ends_with($file, '.md')));
    }

    public function testShelterCodeExamplesMatchRunnableDownloadSources(): void
    {
        $sections = [
            'data-model' => ['database/migrations/20260711000000_create_shelter_api_tables.php', 'src/Modules/ShelterApi/AnimalRepository.php'],
            'handlers' => ['src/Actions/ShelterAnimalActions.php', 'src/Actions/ShelterLookupActions.php', 'src/Modules/ShelterApi/ApiJson.php', 'src/Modules/ShelterApi/NotFoundException.php', 'src/Modules/ShelterApi/ValidationException.php'],
            'filtering-validation' => ['src/Modules/ShelterApi/AnimalService.php', 'src/Modules/ShelterApi/AnimalValidator.php'],
        ];
        foreach ($sections as $chapter => $sources) {
            $markdown = str_replace("\r\n", "\n", (string) file_get_contents(PROJECT_ROOT . '/documentation/projects/shelter-api/' . $chapter . '.md'));
            foreach ($sources as $source) {
                $code = trim(str_replace("\r\n", "\n", (string) file_get_contents(PROJECT_ROOT . '/resources/downloads/shelter-api-completed/' . $source)));
                self::assertStringContainsString("```php\n" . $code . "\n```", $markdown, $chapter . ' differs from runnable ' . $source);
            }
        }
    }

    public function testPublishedCodeUsesCurrentFrameworkContracts(): void
    {
        foreach ($this->markdownFiles() as $file) {
            preg_match_all('/^```(?:php|html|bash|shell)\s*\R([\s\S]*?)^```/m', (string) file_get_contents($file), $blocks);
            foreach ($blocks[1] as $code) {
                self::assertDoesNotMatchRegularExpression('/\$router->(?:get|post|patch|delete|group|dispatch|addMiddleware)\s*\(|\$this->view->render\s*\(|make:(?:controller|handler)\b|namespace (?:Controllers|ApiControllers);/', $code, 'Removed 0.2.x API in ' . $file);
                preg_match_all('/\bCorianderCore\\\\(?:Core|Modules)\\\\[A-Za-z0-9_\\\\]+/', $code, $classes);
                foreach (array_unique($classes[0]) as $class) {
                    self::assertTrue(class_exists($class) || interface_exists($class) || trait_exists($class), 'Unknown framework class ' . $class . ' in ' . $file);
                }
            }
        }
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
        return is_file(PROJECT_ROOT . '/documentation/' . trim($slug, '/') . '.md')
            || is_file(PROJECT_ROOT . '/documentation/' . trim($slug, '/') . '/index.md');
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

    private function assertInternalTargetResolves(string $file, string $target): void
    {
        $path = strtok($target, '#?');
        if (!is_string($path) || $path === '' || preg_match('/^[a-z]+:/i', $path) === 1) {
            return;
        }

        if (str_starts_with($path, '/public/downloads/')) {
            self::assertFileExists(PROJECT_ROOT . $path, $file . ' links to missing download ' . $path);
            return;
        }

        if (str_starts_with($path, '/documentation/')) {
            $slug = substr($path, strlen('/documentation/'));
            self::assertTrue($slug === '' || $this->markdownTargetExists($slug), $file . ' links to missing documentation page ' . $path);
            return;
        }

        if (str_starts_with($path, '/guided-projects/')) {
            self::assertTrue($this->guidedProjectPathExists($path), $file . ' links to missing guided project page ' . $path);
        }
    }
}
