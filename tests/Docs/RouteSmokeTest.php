<?php
declare(strict_types=1);

namespace Tests\Docs;

use CorianderCore\Core\Router\Router;
use CorianderCore\Core\Http\RequestFactory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RouteSmokeTest extends TestCase
{
    #[DataProvider('publicPageProvider')]
    public function testPublicDocumentationRoutesRespond(string $path, string $title): void
    {
        $_SERVER['REQUEST_URI'] = $path;
        $_SESSION = [];

        $router = new Router();
        $response = $router->handle(new ServerRequest('GET', $path));

        self::assertSame(200, $response->getStatusCode(), $path);
        self::assertNotSame('', (string) $response->getBody());
        self::assertStringContainsString('<title>' . $title . ' - CorianderPHP</title>', (string) $response->getBody(), 'Each render must load the current metadata.');
    }

    /**
     * @return array<string,array{string,string}>
     */
    public static function publicPageProvider(): array
    {
        return [
            'home' => ['/', 'CorianderPHP'],
            'documentation index' => ['/documentation', 'Documentation'],
            'forum guide' => ['/guided-projects/forum', 'Forum Guided Project'],
            'shelter guide' => ['/guided-projects/shelter-api', 'Shelter API Guided Project'],
        ];
    }

    public function testOldDocsRouteRedirectsToDocumentation(): void
    {
        $_SERVER['REQUEST_URI'] = '/docs';
        $_SESSION = [];

        $router = new Router();
        $response = $router->handle(new ServerRequest('GET', '/docs'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(['/documentation'], $response->getHeader('Location'));
    }

    public function testHomeAliasRedirectsToCanonicalHomepage(): void
    {
        $response = (new Router())->handle(new ServerRequest('GET', '/home'));
        self::assertSame(301, $response->getStatusCode());
        self::assertSame('/', $response->getHeaderLine('Location'));
    }

    public function testDocumentationSearchApiResponds(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/documentation/search?q=controller&scope=reference';
        $_GET = [];
        $_SESSION = [];

        $router = new Router();
        $request = RequestFactory::fromGlobals(
            serverParams: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/documentation/search?q=controller&scope=reference', 'HTTPS' => 'on'],
            headers: ['Host' => 'docs.example.test:8443'],
            postParams: [], rawBody: '', cookieParams: [], files: [],
        );
        self::assertSame('https://docs.example.test:8443/api/documentation/search?q=controller&scope=reference', (string) $request->getUri());
        $response = $router->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('"ok":true', (string) $response->getBody());
        self::assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('controller', $payload['query']);
        self::assertNotEmpty($payload['results']);
    }

    public function testUnregisteredControllerAliasesCannotBypassAdminMiddleware(): void
    {
        $_SESSION = [];
        $router = new Router();
        self::assertSame(302, $router->handle(new ServerRequest('GET', '/forum-demo/admin'))->getStatusCode());
        self::assertSame(404, $router->handle(new ServerRequest('GET', '/forum-demo/adminUsers'))->getStatusCode());
        self::assertSame(404, $router->handle(new ServerRequest('GET', '/notfound'))->getStatusCode());
    }

    public function testHeadFallsBackToRegisteredGetRoutesAndEmitterSuppressesBody(): void
    {
        $router = new Router();
        $response = $router->handle(new ServerRequest('HEAD', '/documentation'));
        self::assertSame(200, $response->getStatusCode());
        ob_start();
        \CorianderCore\Core\Http\ResponseEmitter::emit($response, 'HEAD');
        self::assertSame('', ob_get_clean());
    }

    public function testDownloadArtifactsExist(): void
    {
        foreach (['forum-completed.zip', 'shelter-api-completed.zip'] as $file) {
            $path = PROJECT_ROOT . '/public/downloads/' . $file;
            self::assertFileExists($path);
            self::assertGreaterThan(1024, filesize($path));
        }
    }
}
