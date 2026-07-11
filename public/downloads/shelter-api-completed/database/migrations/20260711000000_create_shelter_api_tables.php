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
