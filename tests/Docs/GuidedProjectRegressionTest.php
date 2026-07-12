<?php
declare(strict_types=1);

namespace Tests\Docs;

use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ZipArchive;

final class GuidedProjectRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
    }

    public function testForumDemoRoutesPermissionsAndFakeWritesWorkTogether(): void
    {
        $guestTopics = $this->dispatch('GET', '/forum-demo/topics');
        self::assertSame(200, $guestTopics->getStatusCode());
        self::assertStringContainsString('Log in as the member or admin demo account', (string) $guestTopics->getBody());

        $guestWrite = $this->dispatch('POST', '/forum-demo/topics', ['title' => 'Guest topic']);
        self::assertRedirectsTo('/forum-demo/topics', $guestWrite);
        self::assertSame(403, $_SESSION['forum_demo_flash']['status'] ?? null);

        $memberLogin = $this->dispatch('POST', '/forum-demo/login', ['quick_role' => 'member']);
        self::assertRedirectsTo('/forum-demo', $memberLogin);

        $memberTopics = $this->dispatch('GET', '/forum-demo/topics');
        self::assertSame(200, $memberTopics->getStatusCode());

        $memberWrite = $this->dispatch('POST', '/forum-demo/topics', [
            'category_id' => '1',
            'title' => 'How do demo writes work?',
            'body' => 'This should validate but stay unsaved.',
        ]);
        self::assertRedirectsTo('/forum-demo/topics', $memberWrite);
        self::assertSame(200, $_SESSION['forum_demo_flash']['status'] ?? null);

        $memberAdmin = $this->dispatch('GET', '/forum-demo/admin');
        self::assertRedirectsTo('/forum-demo/login', $memberAdmin);

        $adminLogin = $this->dispatch('POST', '/forum-demo/login', ['quick_role' => 'admin']);
        self::assertRedirectsTo('/forum-demo', $adminLogin);
        self::assertSame(1, $_SESSION['forum_demo_user_id'] ?? null);

        $admin = $this->dispatch('GET', '/forum-demo/admin');
        self::assertSame(200, $admin->getStatusCode());
        self::assertStringContainsString('Admin moderation', (string) $admin->getBody());
        self::assertStringContainsString('Moderation queue', (string) $admin->getBody());

        $adminLogin = $this->dispatch('POST', '/forum-demo/login', ['quick_role' => 'admin']);
        self::assertRedirectsTo('/forum-demo', $adminLogin);
        self::assertSame(1, $_SESSION['forum_demo_user_id'] ?? null);

        $moderation = $this->dispatch('POST', '/forum-demo/admin/topics', [
            'return_to' => 'topic',
            'topic_id' => '1',
            'title' => 'How do migrations work?',
            'action' => 'lock',
        ]);
        self::assertRedirectsTo('/forum-demo/topics/1', $moderation);

        $topic = $this->dispatch('GET', '/forum-demo/topics/1');
        self::assertSame(200, $topic->getStatusCode());
        self::assertStringContainsString('Demo mode: the action passed validation but was not saved.', (string) $topic->getBody());
        self::assertStringContainsString('Lock topic', (string) $topic->getBody());
    }

    public function testShelterApiPlaygroundReturnsExpectedJsonAndProtectedDemoWrites(): void
    {
        $cats = $this->dispatch('GET', '/api/playground/shelter/animals?species=cat');
        self::assertSame(200, $cats->getStatusCode());
        $catPayload = $this->json($cats);
        self::assertSame('Milo', $catPayload['data'][0]['name'] ?? null);
        self::assertSame('cat', $catPayload['data'][0]['species'] ?? null);

        $missing = $this->dispatch('GET', '/api/playground/shelter/animals/999');
        self::assertSame(404, $missing->getStatusCode());
        self::assertSame('not_found', $this->json($missing)['error']['code'] ?? null);

        $invalidCreate = $this->dispatchJson('POST', '/api/playground/shelter/animals', []);
        self::assertSame(422, $invalidCreate->getStatusCode());
        $invalidPayload = $this->json($invalidCreate);
        self::assertSame('validation_failed', $invalidPayload['error']['code'] ?? null);
        self::assertArrayHasKey('name', $invalidPayload['error']['fields'] ?? []);
        self::assertArrayHasKey('species', $invalidPayload['error']['fields'] ?? []);
        self::assertArrayHasKey('shelter_id', $invalidPayload['error']['fields'] ?? []);

        $created = $this->dispatchJson('POST', '/api/playground/shelter/animals', [
            'name' => 'Poppy',
            'species' => 'bunny',
            'shelter_id' => 2,
            'age_months' => 7,
        ]);
        self::assertSame(201, $created->getStatusCode());
        $createdPayload = $this->json($created);
        self::assertSame('Poppy', $createdPayload['data']['name'] ?? null);
        self::assertTrue($createdPayload['data']['demo_write'] ?? false);
        self::assertFalse($createdPayload['meta']['persisted'] ?? true);

        $updated = $this->dispatchJson('PATCH', '/api/playground/shelter/animals/1', ['status' => 'reserved']);
        self::assertSame(200, $updated->getStatusCode());
        $updatedPayload = $this->json($updated);
        self::assertSame('reserved', $updatedPayload['data']['status'] ?? null);
        self::assertTrue($updatedPayload['data']['demo_write'] ?? false);
        self::assertFalse($updatedPayload['meta']['persisted'] ?? true);

        $deleted = $this->dispatch('DELETE', '/api/playground/shelter/animals/1');
        self::assertSame(200, $deleted->getStatusCode());
        $deletedPayload = $this->json($deleted);
        self::assertTrue($deletedPayload['data']['deleted'] ?? false);
        self::assertSame(1, $deletedPayload['data']['id'] ?? null);
        self::assertFalse($deletedPayload['meta']['persisted'] ?? true);
    }

    public function testGuidedProjectDownloadPackagesContainRunnableProjectFiles(): void
    {
        $this->assertZipContains('public/downloads/forum-completed.zip', [
            'README.md',
            'src/Routes/forum-demo.php',
            'src/Controllers/ForumDemoController.php',
            'src/ApiControllers/ForumDemoController.php',
            'src/Middleware/ForumDemoAdminMiddleware.php',
            'src/Modules/ForumDemo/Auth/DemoAuth.php',
            'src/Modules/ForumDemo/Data/DemoForumRepository.php',
            'src/Modules/ForumDemo/Permissions/DemoPermissionService.php',
            'src/Modules/ForumDemo/Writes/DemoWriteGuard.php',
            'public/public_views/forum-demo/index.php',
            'public/public_views/forum-demo/topic/index.php',
            'nodejs/src/forum-demo/index.ts',
            'documentation/projects/forum/routes.md',
        ]);

        $this->assertZipContains('public/downloads/shelter-api-completed.zip', [
            'README.md',
            'database/migrations/20260711000000_create_shelter_api_tables.php',
            'public/routes.snippet.php',
            'src/Routes/api/shelter.php',
            'src/ApiControllers/ShelterAnimalController.php',
            'src/ApiControllers/ShelterLookupController.php',
            'src/Modules/ShelterApi/AnimalRepository.php',
            'src/Modules/ShelterApi/AnimalService.php',
            'src/Modules/ShelterApi/AnimalValidator.php',
            'src/Modules/ShelterApi/ApiJson.php',
        ]);
    }

    /**
     * @param array<string,mixed> $parsedBody
     */
    private function dispatch(string $method, string $uri, array $parsedBody = []): ResponseInterface
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? $path : $uri;
        $queryString = parse_url($uri, PHP_URL_QUERY);
        $query = [];
        if (is_string($queryString)) {
            parse_str($queryString, $query);
        }

        $_SERVER['REQUEST_URI'] = $uri;
        $_GET = $query;
        $_POST = strtoupper($method) === 'POST' ? $parsedBody : [];

        $router = new Router();
        $notFound = static fn() => new Response(404, [], 'Not found');
        require PROJECT_ROOT . '/public/routes.php';

        $request = (new ServerRequest($method, $uri))
            ->withQueryParams($query)
            ->withParsedBody($parsedBody);

        return $router->dispatch($request);
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function dispatchJson(string $method, string $uri, array $payload): ResponseInterface
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? $path : $uri;
        $queryString = parse_url($uri, PHP_URL_QUERY);
        $query = [];
        if (is_string($queryString)) {
            parse_str($queryString, $query);
        }

        $_SERVER['REQUEST_URI'] = $uri;
        $_GET = $query;
        $_POST = [];

        $router = new Router();
        $notFound = static fn() => new Response(404, [], 'Not found');
        require PROJECT_ROOT . '/public/routes.php';

        $request = (new ServerRequest($method, $path))
            ->withQueryParams($query)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Stream::create(json_encode($payload, JSON_THROW_ON_ERROR)));

        return $router->dispatch($request);
    }

    /**
     * @return array<string,mixed>
     */
    private function json(ResponseInterface $response): array
    {
        $payload = json_decode((string) $response->getBody(), true);
        self::assertIsArray($payload);
        return $payload;
    }

    private static function assertRedirectsTo(string $location, ResponseInterface $response): void
    {
        self::assertSame(302, $response->getStatusCode());
        self::assertSame([$location], $response->getHeader('Location'));
    }

    /**
     * @param string[] $entries
     */
    private function assertZipContains(string $relativeZipPath, array $entries): void
    {
        $zipPath = PROJECT_ROOT . '/' . $relativeZipPath;
        self::assertFileExists($zipPath);

        $zip = new ZipArchive();
        self::assertTrue($zip->open($zipPath), 'Unable to open ' . $relativeZipPath);

        try {
            foreach ($entries as $entry) {
                self::assertNotFalse($zip->locateName($entry), $relativeZipPath . ' is missing ' . $entry);
            }
        } finally {
            $zip->close();
        }
    }
}
