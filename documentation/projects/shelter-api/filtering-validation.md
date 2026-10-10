# Filtering And Validation

The service coordinates allowed operations; the validator checks input before the repository writes. These are the exact implementations used in the completed SQLite API.

```workflow
Action|Passes query parameters or parsed body into AnimalService.
Service|Chooses a read/write workflow and checks related records.
Validator|Checks create fields or the provided fields of a partial update.
Repository|Runs parameterized SQL and returns stored records.
Action|Formats data or catches a domain exception.
```

## Supported Filters

Use species, status, shelter_id, min_age_months, max_age_months, or search in the collection query. The repository restricts SQL fragments to known filters and binds values; never insert visitor-provided column names into SQL.

## Animal Validator

Create `src/Modules/ShelterApi/AnimalValidator.php` with the following contents:

```php
<?php
declare(strict_types=1);

namespace App\Modules\ShelterApi;

/*
 * Validation belongs before repository writes. Keeping accepted species,
 * statuses, and field messages here lets store() and update() share rules.
 */
final class AnimalValidator
{
    private const SPECIES = ['cat', 'dog', 'bunny', 'bird'];
    private const STATUSES = ['available', 'reserved', 'adopted'];

    public function validateCreate(array $input): array
    {
        $data = [
            'name' => trim((string) ($input['name'] ?? '')),
            'species' => (string) ($input['species'] ?? ''),
            'shelter_id' => (int) ($input['shelter_id'] ?? 0),
            'age_months' => (int) ($input['age_months'] ?? 0),
            'status' => (string) ($input['status'] ?? 'available'),
            'description' => trim((string) ($input['description'] ?? '')),
        ];
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'][] = 'Name is required.';
        }

        if (!in_array($data['species'], self::SPECIES, true)) {
            $errors['species'][] = 'Species must be cat, dog, bunny, or bird.';
        }

        if ($data['shelter_id'] < 1) {
            $errors['shelter_id'][] = 'Shelter is required.';
        }

        if ($data['age_months'] < 0) {
            $errors['age_months'][] = 'Age cannot be negative.';
        }

        if (!in_array($data['status'], self::STATUSES, true)) {
            $errors['status'][] = 'Status must be available, reserved, or adopted.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $data;
    }
}
```

## Animal Service

Create `src/Modules/ShelterApi/AnimalService.php` with the following contents:

```php
<?php
declare(strict_types=1);

namespace App\Modules\ShelterApi;

/*
 * Application workflow layer:
 * controllers call this service. It validates writes, handles not-found rules,
 * and delegates persistence to AnimalRepository.
 */
final class AnimalService
{
    public function __construct(
        private readonly AnimalRepository $animals = new AnimalRepository(),
        private readonly AnimalValidator $validator = new AnimalValidator(),
    ) {
    }

    public function list(array $filters): array
    {
        return $this->animals->list($filters);
    }

    public function find(int $id): array
    {
        $animal = $this->animals->find($id);
        if ($animal === null) {
            throw new NotFoundException('Animal not found.');
        }

        return $animal;
    }

    public function create(array $input): array
    {
        $data = $this->validator->validateCreate($input);
        $id = $this->animals->create($data);
        return $this->find($id);
    }

    public function update(int $id, array $input): array
    {
        $current = $this->find($id);
        $data = $this->validator->validateCreate(array_merge($current, $input));
        $this->animals->update($id, $data);
        return $this->find($id);
    }

    public function archive(int $id): void
    {
        $this->find($id);
        $this->animals->archive($id);
    }

    public function species(): array
    {
        return $this->animals->species();
    }

    public function shelters(): array
    {
        return $this->animals->shelters();
    }
}
```

## PATCH And Validation Errors

POST requires the create fields. PATCH is partial: this service merges submitted fields with the current record, then validates the complete result. Omitted values are preserved. An empty PATCH currently leaves values unchanged; add an explicit rejection if your API contract requires a change.

The validator checks accepted species/status values and required input. Actions turn ValidationException into 422 using ApiJson::error(). Missing or archived records produce NotFoundException and 404. Before deploying, also validate referenced shelter/species rows, field types/lengths, and writable-field allowlists rather than relying only on database constraints.

```http
POST /api/shelter/animals
Content-Type: application/json

{"species":"dragon"}
```

Expect 422 and field errors. Then create a valid animal, PATCH only its status, and verify the name/species are unchanged. DELETE archives the record; subsequent GET should return 404 while the database row remains.

Use the same validator/service from every endpoint. Do not duplicate field rules in routes and actions.

Continue with [Errors And Versioning](/guided-projects/shelter-api/errors).
