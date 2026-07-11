<?php
declare(strict_types=1);

namespace Tests\Docs;

use Modules\Docs\DocumentationRepository;
use Modules\Docs\DocumentationSearch;
use PHPUnit\Framework\TestCase;

final class DocumentationSearchTest extends TestCase
{
    public function testSearchReturnsReferenceResultsInsideReferenceScope(): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search('controller', 'reference');

        self::assertNotEmpty($results);
        foreach ($results as $result) {
            self::assertFalse(str_starts_with($result['page']->slug, 'projects/'));
        }
    }

    public function testSearchCanTargetGuidedProjectScope(): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search('permissions', 'forum');

        self::assertNotEmpty($results);
        foreach ($results as $result) {
            self::assertStringStartsWith('projects/forum', $result['page']->slug);
        }
    }

    public function testEmptySearchReturnsNoResults(): void
    {
        self::assertSame([], (new DocumentationSearch(new DocumentationRepository()))->search('   ', 'reference'));
    }
}
