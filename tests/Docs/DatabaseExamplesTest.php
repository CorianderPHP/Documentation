<?php
declare(strict_types=1);

namespace Tests\Docs;

use CorianderCore\Core\Database\DatabaseHandler;
use CorianderCore\Core\Database\SQLManager;
use PDO;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class DatabaseExamplesTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDocumentedNullableConditionsKeepReadAndWriteScopes(): void
    {
        $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT, tenant_id INTEGER, status TEXT, deleted_at TEXT)');
        $pdo->exec("INSERT INTO users VALUES
            (5, 'a@example.test', 10, 'active', NULL),
            (6, 'b@example.test', 10, 'active', NULL),
            (7, 'c@example.test', 11, 'active', NULL),
            (8, 'd@example.test', 10, 'active', '2026-01-01')");
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

        // Match the schema assumptions and nullable examples in the Database Guide.
        $users = SQLManager::findWhere(['id', 'email'], 'users', [
            'tenant_id' => 10,
            'deleted_at' => null,
        ]);
        self::assertEqualsCanonicalizing([5, 6], array_column($users, 'id'));
        SQLManager::updateWhere('users', ['status' => 'disabled'], ['id' => 5, 'deleted_at' => null]);
        self::assertSame('disabled', $pdo->query('SELECT status FROM users WHERE id = 5')->fetchColumn());
        SQLManager::updateWhere('users', ['status' => 'disabled'], ['id' => 8, 'deleted_at' => null]);
        self::assertSame('active', $pdo->query('SELECT status FROM users WHERE id = 8')->fetchColumn());
        SQLManager::deleteWhere('users', ['tenant_id' => 10, 'deleted_at' => null]);
        self::assertEqualsCanonicalizing([7, 8], $pdo->query('SELECT id FROM users')->fetchAll(PDO::FETCH_COLUMN));
    }
}
