# Shelter Data Model

The API needs a small relational model. Keep lookup data, shelter locations, and animals separate so filtering stays simple and the schema can move from SQLite to MySQL later.

## Tables

- `species` contains `cat`, `dog`, `bunny`, and `bird`.
- `shelters` contains physical or logical shelter locations.
- `animals` contains the public animal records.
- `adoption_requests` is optional, but useful if the API later accepts adoption forms.

## Create a migration

Generate a migration:

```bash
php coriander make:migration CreateShelterApiTables
```

Inside the generated migration, execute the SQLite schema:

```sql
CREATE TABLE IF NOT EXISTS species (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,
    label TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS shelters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    city TEXT NOT NULL,
    country TEXT NOT NULL DEFAULT 'FR'
);

CREATE TABLE IF NOT EXISTS animals (
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
);

CREATE INDEX IF NOT EXISTS idx_animals_species ON animals(species_id);
CREATE INDEX IF NOT EXISTS idx_animals_shelter ON animals(shelter_id);
CREATE INDEX IF NOT EXISTS idx_animals_status ON animals(status);
```

Then run:

```bash
php coriander migrate
```

## Seed data

For a local learning project, seed inside the migration or a small local seed command. The seed data should insert the four supported species and a few animals:

```sql
INSERT OR IGNORE INTO species (slug, label) VALUES
('cat', 'Cats'),
('dog', 'Dogs'),
('bunny', 'Bunnies'),
('bird', 'Birds');

INSERT INTO shelters (name, city, country) VALUES
('North Shelter', 'Lille', 'FR'),
('River Shelter', 'Lyon', 'FR');

INSERT INTO animals (shelter_id, species_id, name, age_months, status, description)
SELECT 1, id, 'Milo', 18, 'available', 'Calm cat looking for an apartment home.' FROM species WHERE slug = 'cat';

INSERT INTO animals (shelter_id, species_id, name, age_months, status, description)
SELECT 1, id, 'Nala', 30, 'reserved', 'Friendly dog that likes long walks.' FROM species WHERE slug = 'dog';

INSERT INTO animals (shelter_id, species_id, name, age_months, status, description)
SELECT 2, id, 'Pepper', 8, 'available', 'Young bunny comfortable with children.' FROM species WHERE slug = 'bunny';

INSERT INTO animals (shelter_id, species_id, name, age_months, status, description)
SELECT 2, id, 'Kiwi', 14, 'available', 'Small bird with a bright song.' FROM species WHERE slug = 'bird';
```

## MySQL changes

For MySQL, replace `INTEGER PRIMARY KEY AUTOINCREMENT` with `INT AUTO_INCREMENT PRIMARY KEY`, replace `TEXT` with `VARCHAR` where a length is known, and use `INSERT IGNORE` instead of `INSERT OR IGNORE`. Keep the same table names and repository methods so controllers do not change.

## Repository access

Use `SQLManager::sqlScript()` for the API reads because the list endpoint needs joins and optional filters:

```php
use CorianderCore\Core\Database\SQLManager;

$row = SQLManager::sqlScript(
    'SELECT animals.id, animals.name, species.slug AS species
     FROM animals
     INNER JOIN species ON species.id = animals.species_id
     WHERE animals.id = :id
     LIMIT 1',
    ['id' => $id]
);
```

This keeps raw SQL inside repository classes and away from controllers.
