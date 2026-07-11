<?php
declare(strict_types=1);

namespace Tests\Docs;

use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

final class RouteSmokeTest extends TestCase
{
    /**
     * @dataProvider publicPageProvider
     */
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
    }

    /**
     * @return array<string,array{string}>
     */
    public static function publicPageProvider(): array
    {
        return [
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
        $_GET = ['q' => 'controller', 'scope' => 'reference'];
        $_SESSION = [];

        $router = new Router();
        $notFound = static fn() => new Response(404, [], 'Not found');
        require PROJECT_ROOT . '/public/routes.php';

        $response = $router->dispatch(new ServerRequest('GET', '/api/documentation/search?q=controller&scope=reference'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('"ok":true', (string) $response->getBody());
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
