<?php
declare(strict_types=1);

namespace Tests\Docs;

use App\Modules\Docs\DocumentationRepository;
use App\Modules\Docs\DocumentationSearch;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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

    public function testSearchFindsInstallationCommands(): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search('php coriander nodejs run install', 'reference');

        self::assertNotEmpty($results);
        self::assertContains('installation', array_map(static fn(array $result): string => $result['page']->slug, $results));
    }

    public function testSearchFindsImageHandlerOptionsApi(): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search('ImageHandler options loading decoding', 'reference');

        self::assertNotEmpty($results);
        self::assertSame('assets', $results[0]['page']->slug);
    }

    public function testSearchFindsCliHelpDocumentation(): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search('php coriander help nodejs --help', 'reference');

        self::assertNotEmpty($results);
        self::assertSame('cli', $results[0]['page']->slug);
    }

    public function testSearchFindsDynamicViewDocumentation(): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search('dynamic view render data', 'reference');

        self::assertNotEmpty($results);
        self::assertSame('dynamic-views', $results[0]['page']->slug);
    }

    public function testSearchFindsStaticViewDocumentation(): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search('static view about template', 'reference');

        self::assertNotEmpty($results);
        self::assertSame('static-views', $results[0]['page']->slug);
    }

    public function testEmptySearchReturnsNoResults(): void
    {
        self::assertSame([], (new DocumentationSearch(new DocumentationRepository()))->search('   ', 'reference'));
    }

    public function testSearchFindsMetadataMigrationGuidance(): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search('upgrade metadata', 'reference');
        self::assertNotEmpty($results);
        self::assertSame('upgrades', $results[0]['page']->slug);
    }

    #[DataProvider('developerQuestions')]
    public function testDeveloperQuestionsFindTheRelevantGuide(string $query, string $slug, ?string $anchor): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search($query, 'reference');
        self::assertNotEmpty($results, $query);
        self::assertSame($slug, $results[0]['page']->slug, $query);
        if ($anchor !== null) {
            self::assertSame($anchor, $results[0]['anchor'], $query);
        }
        self::assertStringContainsString('id="' . $results[0]['anchor'] . '"', $results[0]['page']->html);
    }

    public static function developerQuestions(): array
    {
        return [
            'new page' => ['how do I create a page', 'static-views', 'create-your-page'],
            'dynamic page' => ['dynamic view render data', 'dynamic-views', null],
            'login' => ['authentication', 'sessions', 'login-and-logout'],
            'pagination' => ['pagination', 'database-patterns', 'pagination'],
            'transaction' => ['database transaction', 'database-patterns', 'transactions'],
            'nullable query' => ['SQL NULL', 'database', 'nullable-conditions'],
            'image upload boundary' => ['upload an image', 'assets', 'image-uploads-are-application-logic'],
            'handler synonym' => ['create a controller', 'handlers', null],
            'controller typo' => ['controler', 'handlers', null],
        ];
    }

    public function testExcerptsAndSnippetsDecodeEntitiesWithoutLosingSourceCode(): void
    {
        $results = (new DocumentationSearch(new DocumentationRepository()))->search('SQL NULL', 'reference');
        $result = $results[0];
        self::assertStringNotContainsString('&#039;', $result['excerpt']);
        self::assertStringNotContainsString('&gt;', $result['excerpt']);
        self::assertNotNull($result['snippet']);
        self::assertSame('php', $result['snippet']['language']);
        self::assertStringContainsString("'deleted_at' => null", $result['snippet']['code']);
        self::assertStringContainsString(htmlspecialchars($result['snippet']['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), $result['page']->html);
    }

    public function testUnrelatedAndStopWordQueriesDoNotFabricateMatches(): void
    {
        $search = new DocumentationSearch(new DocumentationRepository());
        self::assertSame([], $search->search('how do I', 'reference'));
        self::assertSame([], $search->search('intergalactic teleportation', 'reference'));
    }

    public function testGuidedProjectMatchesLinkToTheirOwnSections(): void
    {
        foreach (['forum' => 'permissions', 'shelter-api' => 'validation'] as $scope => $query) {
            $results = (new DocumentationSearch(new DocumentationRepository()))->search($query, $scope);
            self::assertNotEmpty($results);
            foreach ($results as $result) {
                self::assertStringStartsWith('projects/' . $scope . '/', $result['page']->slug);
                self::assertStringContainsString('id="' . $result['anchor'] . '"', $result['page']->html);
            }
        }
    }
}
