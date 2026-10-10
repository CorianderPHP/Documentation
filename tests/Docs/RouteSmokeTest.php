<?php
declare(strict_types=1);

namespace Tests\Docs;

use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RouteSmokeTest extends TestCase
{
    #[DataProvider('publicPageProvider')]
    public function testPublicDocumentationRoutesRespond(string $path): void
    {
        $_SERVER['REQUEST_URI'] = $path;
        $_SESSION = [];

        $router = new Router();
        $notFound = static fn() => new Response(404, [], 'Not found');
        require PROJECT_ROOT . '/public/routes.php';

        $response = $router->dispatch(new ServerRequest('GET', $path));

        self::assertSame(200, $response->getStatusCode(), $path);
        self::assertNotSame('', (string) $response->getBody());
        self::assertStringNotContainsString('No configured title', (string) $response->getBody(), 'Each render must load the current metadata.');
    }

    /**
     * @return array<string,array{string}>
     */
    public static function publicPageProvider(): array
    {
        return [
            'home' => ['/'],
            'home alias' => ['/home'],
            'documentation index' => ['/documentation'],
            'forum guide' => ['/guided-projects/forum'],
            'shelter guide' => ['/guided-projects/shelter-api'],
        ];
    }

    public function testOldDocsRouteRedirectsToDocumentation(): void
    {
        $_SERVER['REQUEST_URI'] = '/docs';
        $_SESSION = [];

        $router = new Router();
        $notFound = static fn() => new Response(404, [], 'Not found');
        require PROJECT_ROOT . '/public/routes.php';

        $response = $router->dispatch(new ServerRequest('GET', '/docs'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(['/documentation'], $response->getHeader('Location'));
    }

    public function testDocumentationSearchApiResponds(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/documentation/search?q=controller&scope=reference';
        $_GET = [];
        $_SESSION = [];

        $router = new Router();
        $notFound = static fn() => new Response(404, [], 'Not found');
        require PROJECT_ROOT . '/public/routes.php';

        $response = $router->dispatch((new ServerRequest('GET', '/api/documentation/search?q=controller&scope=reference'))->withQueryParams(['q' => 'controller', 'scope' => 'reference']));

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
        $notFound = static fn() => new Response(404, [], 'Not found');
        $router->setNotFound($notFound);
        require PROJECT_ROOT . '/public/routes.php';

        self::assertSame(302, $router->dispatch(new ServerRequest('GET', '/forum-demo/admin'))->getStatusCode());
        self::assertSame(404, $router->dispatch(new ServerRequest('GET', '/forum-demo/adminUsers'))->getStatusCode());
        self::assertSame(404, $router->dispatch(new ServerRequest('GET', '/notfound'))->getStatusCode());
    }

    public function testHeadFallsBackToRegisteredGetRoutesAndEmitterSuppressesBody(): void
    {
        $router = new Router();
        $notFound = static fn() => new Response(404, [], 'Not found');
        require PROJECT_ROOT . '/public/routes.php';

        $response = $router->dispatch(new ServerRequest('HEAD', '/documentation'));
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
