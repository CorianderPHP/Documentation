<?php
declare(strict_types=1);

namespace Tests\Docs;

use Modules\Docs\DocumentationRepository;
use Modules\Docs\GuidedProjectNavigation;
use Modules\Docs\GuidedProjectRegistry;
use PHPUnit\Framework\TestCase;

final class GuidedProjectNavigationTest extends TestCase
{
    public function testForumNavigationOrderAndPathsComeFromRegistry(): void
    {
        $project = (new GuidedProjectRegistry())->find('forum');
        self::assertNotNull($project);

        $pages = (new DocumentationRepository())->byScope($project->key);
        $navigation = new GuidedProjectNavigation($project);
        $ordered = $navigation->orderedPages($pages);
        $groups = $navigation->grouped($pages);

        self::assertSame('projects/forum/index', $ordered[0]->slug);
        self::assertSame('/guided-projects/forum', $groups['Overview'][0]['path']);
        self::assertSame('What We Are Building', $groups['Overview'][0]['label']);
    }

    public function testAdjacentPagesUseProjectPaths(): void
    {
        $project = (new GuidedProjectRegistry())->find('shelter-api');
        self::assertNotNull($project);

        $pages = (new DocumentationRepository())->byScope($project->key);
        $adjacent = (new GuidedProjectNavigation($project))->adjacent($pages, 'projects/shelter-api/routes');

        self::assertSame('/guided-projects/shelter-api/data-model', $adjacent['previous']['path'] ?? null);
        self::assertSame('/guided-projects/shelter-api/controllers', $adjacent['next']['path'] ?? null);
    }
}
