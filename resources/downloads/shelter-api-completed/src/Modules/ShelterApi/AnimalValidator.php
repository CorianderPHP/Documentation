<?php
declare(strict_types=1);

namespace Modules\ShelterApi;

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
