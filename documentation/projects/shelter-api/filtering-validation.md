# Filtering And Validation

Filtering belongs close to the repository. Validation belongs before writes reach the repository. Keep both reusable so the controllers stay small.

## Supported filters

The list endpoint accepts these query parameters:

- `species`: one of `cat`, `dog`, `bunny`, `bird`.
- `status`: one of `available`, `reserved`, `adopted`.
- `shelter_id`: numeric shelter identifier.
- `min_age_months`: minimum age.
- `max_age_months`: maximum age.
- `search`: case-insensitive text search on name and description.

## Repository method

The repository can build a parameterized query from allowed filters:

```php
public function list(array $filters): array
{
    $sql = 'SELECT animals.*, species.slug AS species, shelters.name AS shelter_name
        FROM animals
        JOIN species ON species.id = animals.species_id
        JOIN shelters ON shelters.id = animals.shelter_id
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

    if (($filters['search'] ?? '') !== '') {
        $sql .= ' AND (LOWER(animals.name) LIKE :search OR LOWER(animals.description) LIKE :search)';
        $params['search'] = '%' . strtolower((string) $filters['search']) . '%';
    }

    $sql .= ' ORDER BY animals.created_at DESC';

    return $this->database->fetchAll($sql, $params);
}
```

## Validation object

Create one validator with explicit rules:

```php
final class AnimalValidator
{
    private const SPECIES = ['cat', 'dog', 'bunny', 'bird'];
    private const STATUSES = ['available', 'reserved', 'adopted'];

    public function validateCreate(array $input): array
    {
        $errors = [];

        if (trim((string) ($input['name'] ?? '')) === '') {
            $errors['name'][] = 'Name is required.';
        }

        if (!in_array((string) ($input['species'] ?? ''), self::SPECIES, true)) {
            $errors['species'][] = 'Species must be cat, dog, bunny, or bird.';
        }

        if (!in_array((string) ($input['status'] ?? 'available'), self::STATUSES, true)) {
            $errors['status'][] = 'Status must be available, reserved, or adopted.';
        }

        if ((int) ($input['age_months'] ?? 0) < 0) {
            $errors['age_months'][] = 'Age cannot be negative.';
        }

        return $errors;
    }
}
```

## Validation response

Invalid input should return HTTP `422`:

```json
{
  "error": {
    "code": "validation_failed",
    "message": "The request body is invalid.",
    "fields": {
      "species": ["Species must be cat, dog, bunny, or bird."]
    }
  }
}
```

## DRY rule

Use the same validator from `store()` and `update()`. For update routes, allow partial input but validate any field that is present.
