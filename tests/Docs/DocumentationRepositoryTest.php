<?php
declare(strict_types=1);

namespace Tests\Docs;

use Modules\Docs\DocumentationRepository;
use PHPUnit\Framework\TestCase;

final class DocumentationRepositoryTest extends TestCase
{
    public function testReferenceScopeExcludesGuidedProjects(): void
    {
        $pages = (new DocumentationRepository())->byScope('reference');

        self::assertNotEmpty($pages);
        foreach ($pages as $page) {
            self::assertFalse(str_starts_with($page->slug, 'projects/'));
        }
    }

    public function testReferenceScopeExcludesLandingPage(): void
    {
        $slugs = array_map(static fn($page): string => $page->slug, (new DocumentationRepository())->byScope('reference'));

        self::assertNotContains('index', $slugs);
    }

    public function testGuidedProjectScopesResolveFromRegistry(): void
    {
        $repository = new DocumentationRepository();

        self::assertNotEmpty($repository->byScope('forum'));
        self::assertNotEmpty($repository->byScope('shelter-api'));
        self::assertSame('Build a Forum with Permissions', $repository->find('projects/forum')?->title);
        self::assertSame('Build a Shelter REST API', $repository->find('projects/shelter-api')?->title);
    }
}
