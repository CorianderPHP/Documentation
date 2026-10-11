<?php
declare(strict_types=1);

namespace Tests\Docs;

use CorianderCore\Core\Bootstrap\SessionBootstrap;
use CorianderCore\Core\Database\DatabaseHandler;
use CorianderCore\Core\Database\SQLManager;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class PracticalExamplesTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testPublishedModulePaginationAndTransactionExamplesRunOnTheFramework(): void
    {
        $database = new DatabaseHandler();
        $pdo = $database->getPDO();
        self::assertNotNull($pdo);
        SQLManager::setDatabaseHandler($database);
        $migration = $this->evaluate($this->block('modules', "return new class"));
        $migration->up($pdo);

        $repositoryCode = $this->block('modules', 'final class PostRepository');
        $paginationCode = $this->block('database-patterns', 'public function publishedPage');
        $end = strrpos($repositoryCode, '}');
        $this->evaluate(substr($repositoryCode, 0, $end) . $paginationCode . "\n}");
        $repository = new \App\Modules\Blog\PostRepository();
        self::assertSame('First post', $repository->published()[0]['title']);
        $plainHandler = $this->evaluate($this->block('modules', 'return static function'));
        self::assertSame('First post', json_decode((string) $plainHandler(new ServerRequest('GET', '/posts'))->getBody(), true)['data'][0]['title']);

        $handler = $this->evaluate($this->block('database-patterns', 'return static function'));
        $response = $handler((new ServerRequest('GET', '/posts'))->withQueryParams(['page' => '1', 'per_page' => '1']));
        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(1, $payload['total']);
        self::assertSame(1, $payload['last_page']);
        self::assertCount(1, $payload['data']);
        self::assertSame([], $repository->publishedPage(2, 1)['data']);
        foreach ([['page' => ['1']], ['page' => '0'], ['per_page' => '101'], ['page' => '100001']] as $query) {
            self::assertSame(422, $handler((new ServerRequest('GET', '/posts'))->withQueryParams($query))->getStatusCode());
        }
        $pdo->exec("DELETE FROM posts");
        self::assertSame([], $repository->published());
        self::assertSame(0, $repository->publishedPage(1)['total']);
        self::assertSame(1, $repository->publishedPage(1)['last_page']);

        $auditMigration = $this->evaluate($this->block('database-patterns', 'return new class'));
        $auditMigration->up($pdo);
        $pdo->exec("INSERT INTO posts (id, title, body, status, created_at) VALUES
            (2, 'Commit', 'Example', 'draft', '2026-01-01'),
            (3, 'Rollback', 'Example', 'draft', '2026-01-01')");
        $this->evaluate($this->block('database-patterns', 'final class PublishPost'));
        $service = new \App\Modules\Blog\PublishPost($database);
        $service->publish(2);
        self::assertSame('published', $pdo->query('SELECT status FROM posts WHERE id = 2')->fetchColumn());
        self::assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM post_events WHERE post_id = 2')->fetchColumn());
        $pdo->exec('DROP TABLE post_events');
        try {
            $service->publish(3);
            self::fail('An audit failure must propagate.');
        } catch (\PDOException) {
            self::assertSame('draft', $pdo->query('SELECT status FROM posts WHERE id = 3')->fetchColumn());
            self::assertFalse($pdo->inTransaction());
        }
        try {
            $service->publish(999);
            self::fail('A missing post must not be treated as published.');
        } catch (RuntimeException $exception) {
            self::assertSame('Post not found.', $exception->getMessage());
            self::assertFalse($pdo->inTransaction());
        }
        $pdo->beginTransaction();
        try {
            $service->publish(2);
            self::fail('Nested transactions are not supported by this example.');
        } catch (RuntimeException $exception) {
            self::assertSame('This service must own its transaction.', $exception->getMessage());
            self::assertTrue($pdo->inTransaction());
        } finally {
            $pdo->rollBack();
        }
        $database->close();
        self::assertNull($database->getPDO());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testPublishedAuthenticationHandlersAndDirectoryGateWorkTogether(): void
    {
        SessionBootstrap::start();
        $_SESSION = [];
        $database = new DatabaseHandler();
        $pdo = $database->getPDO();
        self::assertNotNull($pdo);
        SQLManager::setDatabaseHandler($database);
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT UNIQUE, password_hash TEXT)');
        $seed = $pdo->prepare('INSERT INTO users VALUES (1, :email, :hash)');
        $seed->execute(['email' => 'member@example.test', 'hash' => password_hash('correct-password', PASSWORD_DEFAULT)]);
        $login = $this->evaluate($this->block('sessions', 'filter_var($email'));
        $logout = $this->evaluate($this->block('sessions', 'unset($_SESSION'));
        $gate = $this->evaluate($this->block('sessions', 'implements MiddlewareInterface'))[0];
        $next = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new \Nyholm\Psr7\Response(200);
            }
        };
        $get = new ServerRequest('GET', '/members');
        self::assertSame('/login', $gate->process($get, $next)->getHeaderLine('Location'));
        $post = new ServerRequest('POST', '/login');
        foreach ([['email' => []], ['email' => 'member@example.test', 'password' => []]] as $body) {
            self::assertSame(422, $login($post->withParsedBody($body))->getStatusCode());
        }
        self::assertSame(401, $login($post->withParsedBody(['email' => 'member@example.test', 'password' => 'wrong']))->getStatusCode());
        self::assertArrayNotHasKey('user_id', $_SESSION);
        $oldId = session_id();
        $success = $login($post->withParsedBody(['email' => 'member@example.test', 'password' => 'correct-password', 'role' => 'admin']));
        self::assertSame(303, $success->getStatusCode());
        self::assertSame('/members', $success->getHeaderLine('Location'));
        self::assertNotSame($oldId, session_id());
        self::assertSame(1, $_SESSION['user_id']);
        self::assertArrayNotHasKey('role', $_SESSION);
        self::assertSame(200, $gate->process($get, $next)->getStatusCode());
        self::assertSame(303, $logout(new ServerRequest('POST', '/logout'))->getStatusCode());
        self::assertArrayNotHasKey('user_id', $_SESSION);
        self::assertSame('/login', $gate->process($get, $next)->getHeaderLine('Location'));
        session_destroy();
    }

    private function block(string $slug, string $needle): string
    {
        $markdown = (string) file_get_contents(PROJECT_ROOT . '/documentation/' . $slug . '.md');
        preg_match_all('/```php\r?\n(.*?)\r?\n```/s', $markdown, $matches);
        foreach ($matches[1] as $code) {
            if (str_contains($code, $needle)) {
                return $code;
            }
        }
        self::fail('Missing published PHP example: ' . $slug . ' / ' . $needle);
    }

    private function evaluate(string $code): mixed
    {
        return eval(preg_replace('/^<\?php\s*/', '', $code));
    }
}
