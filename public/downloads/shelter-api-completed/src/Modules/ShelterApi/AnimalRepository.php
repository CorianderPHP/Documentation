<?php
declare(strict_types=1);

namespace Modules\ShelterApi;

use CorianderCore\Core\Database\SQLManager;

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
        if ($result === true || $result === []) {
            return [];
        }

        return array_is_list($result) ? $result : [$result];
    }
}
