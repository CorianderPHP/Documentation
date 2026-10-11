<?php
declare(strict_types=1);

namespace Tests\Docs;

use App\Middleware\NotFoundMiddleware;
use App\Modules\Docs\SiteView;
use CorianderCore\Core\Router\Router;
use CorianderCore\Core\Support\PublicUrl;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PageAssetsTest extends TestCase
{
    public function testRenderedPagesLoadOnlyTheirInheritedModules(): void
    {
        $router = new Router();
        foreach ([
            '/documentation/page-scripts' => ['documentation/_script.js'],
            '/guided-projects/forum' => ['guided-projects/_script.js'],
            '/guided-projects/shelter-api/playground' => ['guided-projects/_script.js', 'guided-projects/shelter-api/_script.js'],
            '/forum-demo/topics/1' => ['forum-demo/_script.js'],
        ] as $path => $expected) {
            $response = $router->handle(new ServerRequest('GET', $path));
            self::assertSame(200, $response->getStatusCode(), $path);
            $html = (string) $response->getBody();
            preg_match_all('~<script type="module" src="([^"]+)"~', $html, $tags);
            self::assertCount(count($expected), $tags[1], $path);
            foreach ($expected as $index => $entry) {
                self::assertStringContainsString('/assets/js/pages/' . $entry . '?', $tags[1][$index]);
                self::assertLessThan(strpos($html, '<footer'), strpos($html, $tags[1][$index]));
            }
            self::assertStringNotContainsString('/assets/js/documentation-code/', $html);
        }
    }

    public function testAllEmittedModulesAndSharedImportsExist(): void
    {
        $directory = PROJECT_ROOT . '/public/assets/js/pages';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        $count = 0;
        foreach ($files as $file) {
            if ($file->getExtension() !== 'js') {
                continue;
            }
            $count++;
            $code = (string) file_get_contents($file->getPathname());
            self::assertMatchesRegularExpression('/\A\/\/ coriander-version:[a-f0-9]{64}\n/', $code);
            preg_match_all('/(?:\n|;)import\s*(?:[^;"\']+?from\s*)?["\']([^"\']+)["\']/', $code, $imports);
            foreach ($imports[1] as $import) {
                self::assertStringStartsWith('.', $import, 'Unexpected external import');
                self::assertFileExists(dirname($file->getPathname()) . '/' . $import);
            }
        }
        self::assertGreaterThan(0, $count);
    }

    public function testJsonResponsesDoNotReceiveLayoutOrScriptTags(): void
    {
        $response = (new Router())->handle(new ServerRequest('GET', '/api/playground/shelter/animals'));
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        self::assertIsArray(json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR));
        self::assertStringNotContainsString('<script', (string) $response->getBody());
    }

    public function testDirectHomepageRetainsMetadataAndLayouts(): void
    {
        $html = (string) (new Router())->handle(new ServerRequest('GET', '/'))->getBody();
        self::assertStringContainsString('<title>CorianderPHP - CorianderPHP</title>', $html);
        self::assertStringContainsString('<meta name="description" content="CorianderPHP documentation and guided projects.">', $html);
        self::assertSame(1, substr_count($html, '<header '));
        self::assertSame(1, substr_count($html, '<footer '));
        self::assertSame(1, substr_count($html, '</footer>'));
        self::assertStringContainsString('CorianderPHP - ' . date('Y') . '</span>', $html);
        self::assertStringContainsString('Open documentation', $html);
    }

    public function testMetadataOverridesRemainSeparateFromApplicationPageObjects(): void
    {
        $html = (string) (new SiteView())->response('notfound', [
            'page' => (object) ['title' => 'Application content, not metadata'],
            'metadata' => ['title' => 'Guide & <example>', 'description' => 'A & B'],
        ], 404)->getBody();
        self::assertStringContainsString('<title>Guide &amp; &lt;example&gt; - CorianderPHP</title>', $html);
        self::assertStringContainsString('<meta name="description" content="A &amp; B">', $html);
        self::assertStringNotContainsString('&amp;amp;', $html);
    }

    public function testBuiltStylesheetCarriesTheContentVersionUsedByTheLayout(): void
    {
        $css = (string) file_get_contents(PROJECT_ROOT . '/public/assets/css/output.css');
        self::assertSame(1, preg_match('~\A/\* coriander-version:([a-f0-9]{64}) \*/\n~', $css, $marker));
        self::assertSame(hash('sha256', substr($css, strlen($marker[0]))), $marker[1]);
        $url = PublicUrl::versionedAsset('assets/css/output.css');
        self::assertStringEndsWith('?' . $marker[1], $url);
        $html = (string) (new Router())->handle(new ServerRequest('GET', '/'))->getBody();
        self::assertStringContainsString('href="' . $url . '"', $html);
    }

    public function testCustom404KeepsUnrelatedHeadersAndRemovesStaleRepresentationHeaders(): void
    {
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(404, ['X-Request-Id' => 'example', 'Content-Length' => '3', 'ETag' => 'old'], 'old');
            }
        };
        foreach (['/missing', '/api/missing'] as $path) {
            $response = (new NotFoundMiddleware())->process(new ServerRequest('GET', $path), $handler);
            self::assertSame(404, $response->getStatusCode());
            self::assertSame('example', $response->getHeaderLine('X-Request-Id'));
            self::assertFalse($response->hasHeader('Content-Length'));
            self::assertFalse($response->hasHeader('ETag'));
            self::assertNotSame('old', (string) $response->getBody());
        }
    }
}
