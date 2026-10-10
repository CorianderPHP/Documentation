<?php
declare(strict_types=1);

namespace Tests\Docs;

use App\Modules\Docs\DocumentationRepository;
use App\Modules\Docs\GuidedProjectRegistry;
use App\Modules\Docs\GuidedProjectNavigation;
use App\Modules\ForumDemo\Data\DemoForumRepository;
use CorianderCore\Core\Router\Router;
use CorianderCore\Core\Router\ViewRenderer;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class FrameworkCompatibilityTest extends TestCase
{
    public function testEveryReferenceAndGuidedChapterRenders(): void
    {
        $repository = new DocumentationRepository();
        $paths = array_map(static fn ($page) => '/documentation/' . $page->slug, $repository->byScope('reference'));
        foreach ((new GuidedProjectRegistry())->all() as $project) {
            foreach ((new GuidedProjectNavigation($project))->grouped($repository->byScope($project->key)) as $items) {
                foreach ($items as $item) {
                    $paths[] = $item['path'];
                }
            }
        }
        $router = new Router();
        foreach ($paths as $path) {
            $response = $router->handle(new ServerRequest('GET', $path));
            self::assertSame(200, $response->getStatusCode(), $path);
            self::assertStringContainsString('<html', (string) $response->getBody(), $path);
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testPublicDocumentationDoesNotStartASession(): void
    {
        self::assertSame(PHP_SESSION_NONE, session_status());
        $response = (new Router())->handle(new ServerRequest('GET', '/documentation/routing'));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(PHP_SESSION_NONE, session_status());
    }

    public function testForumTemplateUsesEscapedDataWithoutDoubleEncoding(): void
    {
        $topic = (new DemoForumRepository())->topic(1);
        $topic['title'] = 'A & B <script>alert(1)</script>';
        $response = (new ViewRenderer())->response('forum-demo/topic', [
            'topic' => $topic,
            'replies' => [],
            'permissions' => [],
        ]);
        $html = (string) $response->getBody();
        self::assertStringContainsString('A &amp; B &lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringNotContainsString('A &amp;amp; B', $html);
        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function testRenamedHandlerPagesKeepOldBookmarksWorking(): void
    {
        $router = new Router();
        foreach ([
            '/documentation/controllers' => '/documentation/handlers',
            '/guided-projects/forum/controllers' => '/guided-projects/forum/handlers',
            '/guided-projects/shelter-api/controllers' => '/guided-projects/shelter-api/handlers',
        ] as $old => $new) {
            $response = $router->handle(new ServerRequest('GET', $old));
            self::assertSame(301, $response->getStatusCode());
            self::assertSame($new, $response->getHeaderLine('Location'));
        }
    }

    public function testUnsupportedApiMethodPreservesAllowHeader(): void
    {
        $response = (new Router())->handle(new ServerRequest('PUT', '/api/playground/shelter/animals/1'));
        self::assertSame(405, $response->getStatusCode());
        self::assertStringContainsString('PATCH', $response->getHeaderLine('Allow'));
        self::assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
    }
}
