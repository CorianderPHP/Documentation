<?php
declare(strict_types=1);

namespace Tests\Docs;

use CorianderCore\Core\Database\DatabaseHandler;
use CorianderCore\Core\Database\Migrations\MigrationManager;
use CorianderCore\Core\Database\SQLManager;
use CorianderCore\Core\Router\Router;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Stream;
use PDO;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class DownloadedShelterApiTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCompletedDownloadMigratesAndPersistsApiWrites(): void
    {
        $package = PROJECT_ROOT . '/public/downloads/shelter-api-completed';
        $loader = static function (string $class) use ($package): void {
            foreach (['Modules\\' => 'src/Modules/', 'ApiControllers\\' => 'src/ApiControllers/'] as $prefix => $directory) {
                if (str_starts_with($class, $prefix)) {
                    $file = $package . '/' . $directory . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                    if (is_file($file)) {
                        require $file;
                    }
                }
            }
        };
        spl_autoload_register($loader, true, true);

        // Use real framework SQL and migrations, but never the site's database.
        $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $database = new class($pdo) extends DatabaseHandler {
            public function __construct(private PDO $connection)
            {
                $this->setAutoClose(false);
            }

            public function getPDO(): ?PDO
            {
                return $this->connection;
            }
        };
        SQLManager::setDatabaseHandler($database);
        $migrations = new MigrationManager($pdo, 'sqlite', $package . '/database/migrations');
        self::assertSame(1, $migrations->migrate()['applied']);
        self::assertSame(0, $migrations->migrate()['applied'], 'Running migrate again must not duplicate seed data.');

        $router = new Router();
        (require $package . '/src/Routes/api/shelter.php')($router);
        $get = $router->dispatch(new ServerRequest('GET', '/api/shelter/animals/1'));
        self::assertSame(200, $get->getStatusCode());
        self::assertSame('Milo', json_decode((string) $get->getBody(), true, 512, JSON_THROW_ON_ERROR)['data']['name']);

        $create = $router->dispatch($this->jsonRequest('POST', '/api/shelter/animals', [
            'name' => 'Poppy', 'species' => 'bunny', 'shelter_id' => 2, 'age_months' => 7,
        ]));
        self::assertSame(201, $create->getStatusCode());
        $id = json_decode((string) $create->getBody(), true, 512, JSON_THROW_ON_ERROR)['data']['id'];
        self::assertGreaterThan(4, $id);
        self::assertSame('Poppy', $pdo->query('SELECT name FROM animals WHERE id = ' . (int) $id)->fetchColumn());

        $update = $router->dispatch($this->jsonRequest('PATCH', '/api/shelter/animals/' . $id, ['status' => 'reserved']));
        self::assertSame(200, $update->getStatusCode());
        self::assertSame('reserved', $pdo->query('SELECT status FROM animals WHERE id = ' . (int) $id)->fetchColumn());
        self::assertSame('available', $pdo->query('SELECT status FROM animals WHERE id = 1')->fetchColumn());

        $invalid = $router->dispatch($this->jsonRequest('POST', '/api/shelter/animals', []));
        self::assertSame(422, $invalid->getStatusCode());
        self::assertSame(5, (int) $pdo->query('SELECT COUNT(*) FROM animals')->fetchColumn());

        $delete = $router->dispatch(new ServerRequest('DELETE', '/api/shelter/animals/' . $id));
        self::assertSame(200, $delete->getStatusCode());
        self::assertNotFalse($pdo->query('SELECT archived_at FROM animals WHERE id = ' . (int) $id)->fetchColumn());
        self::assertNotNull($pdo->query('SELECT archived_at FROM animals WHERE id = ' . (int) $id)->fetchColumn());
        self::assertSame(404, $router->dispatch(new ServerRequest('GET', '/api/shelter/animals/' . $id))->getStatusCode());
        self::assertSame(1, $migrations->rollback()['rolled_back']);
        self::assertSame(0, (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'animals'")->fetchColumn());
    }

    private function jsonRequest(string $method, string $uri, array $payload): ServerRequest
    {
        return (new ServerRequest($method, $uri))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Stream::create(json_encode($payload, JSON_THROW_ON_ERROR)));
    }
}
