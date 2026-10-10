# Shelter Data Model

Use the same real SQLite schema as the completed download. The species and shelters are lookup tables; animals reference them and keep an archived_at marker for soft deletion.

## Generate The Migration

```bash
php coriander make:migration CreateShelterApiTables
```

Edit the newly generated timestamped file under database/migrations. **Use that file instead of creating a second migration with the download's timestamp**. Replace its contents with the migration below. Its up/down methods receive a PDO connection, and the framework tracks whether it has already run.

## Complete SQLite Migration

Create `database/migrations/20260711000000_create_shelter_api_tables.php` with the following contents:

```php
<?php
declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS species (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            label TEXT NOT NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS shelters (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            city TEXT NOT NULL,
            country TEXT NOT NULL DEFAULT 'FR'
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS animals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            shelter_id INTEGER NOT NULL,
            species_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            age_months INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'available',
            description TEXT NOT NULL DEFAULT '',
            archived_at TEXT DEFAULT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (shelter_id) REFERENCES shelters(id),
            FOREIGN KEY (species_id) REFERENCES species(id)
        )");

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_animals_species ON animals(species_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_animals_shelter ON animals(shelter_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_animals_status ON animals(status)');

        $pdo->exec("INSERT OR IGNORE INTO species (slug, label) VALUES
            ('cat', 'Cats'),
            ('dog', 'Dogs'),
            ('bunny', 'Bunnies'),
            ('bird', 'Birds')");

        $pdo->exec("INSERT INTO shelters (name, city, country) VALUES
            ('North Shelter', 'Lille', 'FR'),
            ('River Shelter', 'Lyon', 'FR')");

        $pdo->exec("INSERT INTO animals (shelter_id, species_id, name, age_months, status, description)
            SELECT 1, id, 'Milo', 18, 'available', 'Calm cat looking for an apartment home.' FROM species WHERE slug = 'cat'");
        $pdo->exec("INSERT INTO animals (shelter_id, species_id, name, age_months, status, description)
            SELECT 1, id, 'Nala', 30, 'reserved', 'Friendly dog that likes long walks.' FROM species WHERE slug = 'dog'");
        $pdo->exec("INSERT INTO animals (shelter_id, species_id, name, age_months, status, description)
            SELECT 2, id, 'Pepper', 8, 'available', 'Young bunny comfortable with children.' FROM species WHERE slug = 'bunny'");
        $pdo->exec("INSERT INTO animals (shelter_id, species_id, name, age_months, status, description)
            SELECT 2, id, 'Kiwi', 14, 'available', 'Small bird with a bright song.' FROM species WHERE slug = 'bird'");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS animals');
        $pdo->exec('DROP TABLE IF EXISTS shelters');
        $pdo->exec('DROP TABLE IF EXISTS species');
    }
};
```

## Apply And Verify

```bash
php coriander migrate:status
php coriander migrate
```

With DB_TYPE=sqlite and DB_NAME=database/shelter.sqlite, this creates tables and seeds four species, two shelters, and four animals. Re-running migrate does not repeat an applied migration. Do not edit an applied migration in production; add a new one.

Before requesting the API, check that the SQLite file was created and all four animals exist. The database directory must be writable, including migration lock files.

## Repository Boundary

Handlers never build SQL. The repository uses SQLManager::sqlScript() with bound parameters, returns arrays, and keeps archived records out of public reads.

## Animal Repository

Create `src/Modules/ShelterApi/AnimalRepository.php` with the following contents:

```php
<?php
declare(strict_types=1);

namespace App\Modules\ShelterApi;

use CorianderCore\Core\Database\SQLManager;

/*
 * Persistence layer:
 * only repositories should know SQL details. Controllers and services call
 * methods like list(), find(), and create() instead of building queries.
 */
final class AnimalRepository
{
    public function list(array $filters): array
    {
        $sql = 'SELECT animals.id, animals.name, animals.age_months, animals.status, animals.description,
                    animals.created_at, animals.updated_at, species.slug AS species, species.label AS species_label,
                    shelters.id AS shelter_id, shelters.name AS shelter_name, shelters.city AS shelter_city
                FROM animals
                INNER JOIN species ON species.id = animals.species_id
                INNER JOIN shelters ON shelters.id = animals.shelter_id
                WHERE animals.archived_at IS NULL';
        $params = [];

        if (($filters['species'] ?? '') !== '') {
            $sql .= ' AND species.slug = :species';
            $params['species'] = (string) $filters['species'];
        }

        if (($filters['status'] ?? '') !== '') {
            $sql .= ' AND animals.status = :status';
            $params['status'] = (string) $filters['status'];
        }

        if (($filters['shelter_id'] ?? '') !== '') {
            $sql .= ' AND shelters.id = :shelter_id';
            $params['shelter_id'] = (int) $filters['shelter_id'];
        }

        if (($filters['search'] ?? '') !== '') {
            $sql .= ' AND (LOWER(animals.name) LIKE :search OR LOWER(animals.description) LIKE :search)';
            $params['search'] = '%' . strtolower((string) $filters['search']) . '%';
        }

        $sql .= ' ORDER BY animals.created_at DESC';

        return $this->rows(SQLManager::sqlScript($sql, $params));
    }

    public function find(int $id): ?array
    {
        $row = SQLManager::sqlScript(
            'SELECT animals.id, animals.name, animals.age_months, animals.status, animals.description,
                    animals.created_at, animals.updated_at, species.slug AS species, shelters.id AS shelter_id,
                    shelters.name AS shelter_name
             FROM animals
             INNER JOIN species ON species.id = animals.species_id
             INNER JOIN shelters ON shelters.id = animals.shelter_id
             WHERE animals.id = :id AND animals.archived_at IS NULL
             LIMIT 1',
            ['id' => $id]
        );

        return $row === [] ? null : $row;
    }

    public function create(array $data): int
    {
        SQLManager::sqlScript(
            'INSERT INTO animals (shelter_id, species_id, name, age_months, status, description)
             SELECT :shelter_id, species.id, :name, :age_months, :status, :description
             FROM species
             WHERE species.slug = :species',
            [
                'shelter_id' => (int) $data['shelter_id'],
                'name' => (string) $data['name'],
                'age_months' => (int) $data['age_months'],
                'status' => (string) $data['status'],
                'description' => (string) $data['description'],
                'species' => (string) $data['species'],
            ]
        );

        $row = SQLManager::sqlScript('SELECT MAX(id) AS id FROM animals');
        return (int) ($row['id'] ?? 0);
    }

    public function update(int $id, array $data): void
    {
        SQLManager::sqlScript(
            'UPDATE animals
             SET shelter_id = :shelter_id,
                 species_id = (SELECT id FROM species WHERE slug = :species LIMIT 1),
                 name = :name,
                 age_months = :age_months,
                 status = :status,
                 description = :description,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND archived_at IS NULL',
            [
                'id' => $id,
                'shelter_id' => (int) $data['shelter_id'],
                'species' => (string) $data['species'],
                'name' => (string) $data['name'],
                'age_months' => (int) $data['age_months'],
                'status' => (string) $data['status'],
                'description' => (string) $data['description'],
            ]
        );
    }

    public function archive(int $id): void
    {
        SQLManager::sqlScript(
            'UPDATE animals SET archived_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND archived_at IS NULL',
            ['id' => $id]
        );
    }

    public function species(): array
    {
        return $this->rows(SQLManager::sqlScript('SELECT id, slug, label FROM species ORDER BY label ASC'));
    }

    public function shelters(): array
    {
        return $this->rows(SQLManager::sqlScript('SELECT id, name, city, country FROM shelters ORDER BY name ASC'));
    }

    private function rows(array|bool $result): array
    {
        // sqlScript returns one row for single-row results and a list for many rows.
        if ($result === true || $result === []) {
            return [];
        }

        return array_is_list($result) ? $result : [$result];
    }
}
```

## MySQL Adaptation

Use DB_TYPE=mysql and configure host, port, database, credentials, and charset. Create the database separately. Review the migration before applying it:

- Replace INTEGER PRIMARY KEY AUTOINCREMENT with INT AUTO_INCREMENT PRIMARY KEY.
- Use matching types for foreign keys and indexed VARCHAR columns instead of SQLite TEXT.
- Replace INSERT OR IGNORE with INSERT IGNORE.
- Adapt SQLite PRAGMA and IF NOT EXISTS index syntax rather than sending it to MySQL unchanged.
- Test timestamps, foreign keys, transactions, and the same API requests on MySQL.

Keep the repository's method interface so action code does not change. The download targets SQLite; it is not a drop-in MySQL migration.

Continue with [REST Routes](/guided-projects/shelter-api/routes).
