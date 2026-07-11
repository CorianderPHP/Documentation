<?php
declare(strict_types=1);

namespace Modules\Docs;

final class DocumentationRepository
{
    public function __construct(
        private readonly string $docsPath = PROJECT_ROOT . '/docs',
        private readonly MarkdownRenderer $renderer = new MarkdownRenderer(),
        private readonly GuidedProjectRegistry $projectRegistry = new GuidedProjectRegistry(),
    ) {
    }

    /**
     * @return DocumentationPage[]
     */
    public function all(): array
    {
        $files = $this->collectMarkdownFiles();
        $pages = [];
        foreach ($files as $file) {
            $page = $this->pageFromFile($file);
            if ($page !== null) {
                $pages[] = $page;
            }
        }

        usort($pages, static fn(DocumentationPage $left, DocumentationPage $right): int => strcmp($left->slug, $right->slug));
        return $pages;
    }

    /**
     * @return array<string, DocumentationPage[]>
     */
    public function grouped(string $scope = 'all'): array
    {
        $groups = [
            'Start Here' => [],
            'Reference' => [],
            'Project: Forum' => [],
            'Project: Shelter API' => [],
        ];

        foreach ($this->byScope($scope) as $page) {
            $groups[$page->section] ??= [];
            $groups[$page->section][] = $page;
        }

        return array_filter($groups, static fn(array $pages): bool => $pages !== []);
    }

    /**
     * @return DocumentationPage[]
     */
    public function byScope(string $scope): array
    {
        $scope = $this->normalizeScope($scope);
        $projectRegistry = $this->projectRegistry;

        return array_values(array_filter($this->all(), static function (DocumentationPage $page) use ($scope, $projectRegistry): bool {
            if ($scope !== 'all' && $scope !== 'reference') {
                $project = $projectRegistry->find($scope);
                return $project !== null && $project->slugBelongsToProject($page->slug);
            }

            if ($scope === 'reference') {
                return $projectRegistry->projectForSlug($page->slug) === null
                    && !in_array($page->slug, ['index', 'forum-project'], true);
            }

            return true;
        }));
    }

    public function find(string $slug): ?DocumentationPage
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '') {
            return null;
        }

        $file = $this->docsPath . '/' . $slug . '.md';
        if (is_file($file)) {
            return $this->pageFromFile($file);
        }

        $indexFile = $this->docsPath . '/' . $slug . '/index.md';
        return is_file($indexFile) ? $this->pageFromFile($indexFile) : null;
    }

    /**
     * @return string[]
     */
    private function collectMarkdownFiles(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->docsPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isFile() && strtolower($item->getExtension()) === 'md') {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }

    private function pageFromFile(string $file): ?DocumentationPage
    {
        $markdown = file_get_contents($file);
        if (!is_string($markdown)) {
            return null;
        }

        $relative = str_replace('\\', '/', substr($file, strlen(rtrim($this->docsPath, '\\/')) + 1));
        $slug = preg_replace('/\.md$/', '', $relative) ?? basename($file, '.md');
        $title = $this->extractTitle($markdown) ?? ucwords(str_replace('-', ' ', $slug));
        $description = $this->extractDescription($markdown, $title);
        $rendered = $this->renderer->render($markdown);
        $section = $this->sectionForSlug($slug);

        return new DocumentationPage($slug, $title, $description, $section, $markdown, $rendered['html'], $rendered['headings']);
    }

    private function extractTitle(string $markdown): ?string
    {
        if (preg_match('/^#\s+(.+)$/m', $markdown, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    private function extractDescription(string $markdown, string $title): string
    {
        $plain = trim((string) preg_replace('/[#`*_>\-\[\]\(\)]/', '', $markdown));
        $plain = trim((string) preg_replace('/\s+/', ' ', $plain));
        if ($plain === '') {
            return $title;
        }

        return substr($plain, 0, 160);
    }

    private function normalizeSlug(string $slug): string
    {
        $slug = strtolower(trim(str_replace('\\', '/', $slug), '/'));
        return preg_match('/^[a-z0-9-\/]+$/', $slug) === 1 && !str_contains($slug, '..') ? $slug : '';
    }

    private function normalizeScope(string $scope): string
    {
        if (in_array($scope, ['all', 'reference'], true)) {
            return $scope;
        }

        return $this->projectRegistry->find($scope) !== null ? $scope : 'all';
    }

    private function sectionForSlug(string $slug): string
    {
        $project = $this->projectRegistry->projectForSlug($slug);
        if ($project !== null) {
            return 'Project: ' . $project->navTitle;
        }

        if (in_array($slug, ['index', 'concepts', 'cli', 'routing', 'controllers', 'middleware', 'views', 'database'], true)) {
            return 'Start Here';
        }

        return 'Reference';
    }
}
