<?php
declare(strict_types=1);

namespace Modules\ShelterPlayground;

final class ShelterPlaygroundData
{
    public function animals(array $filters = []): array
    {
        $animals = $this->allAnimals();

        if (($filters['species'] ?? '') !== '') {
            $animals = array_filter($animals, static fn(array $animal): bool => $animal['species'] === (string) $filters['species']);
        }

        if (($filters['status'] ?? '') !== '') {
            $animals = array_filter($animals, static fn(array $animal): bool => $animal['status'] === (string) $filters['status']);
        }

        if (($filters['search'] ?? '') !== '') {
            $needle = strtolower((string) $filters['search']);
            $animals = array_filter($animals, static fn(array $animal): bool => str_contains(strtolower($animal['name'] . ' ' . $animal['description']), $needle));
        }

        return array_values($animals);
    }

    public function animal(int $id): ?array
    {
        foreach ($this->allAnimals() as $animal) {
            if ($animal['id'] === $id) {
                return $animal;
            }
        }

        return null;
    }

    public function species(): array
    {
        return [
            ['id' => 1, 'slug' => 'cat', 'label' => 'Cats'],
            ['id' => 2, 'slug' => 'dog', 'label' => 'Dogs'],
            ['id' => 3, 'slug' => 'bunny', 'label' => 'Bunnies'],
            ['id' => 4, 'slug' => 'bird', 'label' => 'Birds'],
        ];
    }

    public function shelters(): array
    {
        return [
            ['id' => 1, 'name' => 'North Shelter', 'city' => 'Lille', 'country' => 'FR'],
            ['id' => 2, 'name' => 'River Shelter', 'city' => 'Lyon', 'country' => 'FR'],
        ];
    }

    private function allAnimals(): array
    {
        return [
            ['id' => 1, 'name' => 'Milo', 'species' => 'cat', 'shelter_id' => 1, 'shelter_name' => 'North Shelter', 'age_months' => 18, 'status' => 'available', 'description' => 'Calm cat looking for an apartment home.'],
            ['id' => 2, 'name' => 'Nala', 'species' => 'dog', 'shelter_id' => 1, 'shelter_name' => 'North Shelter', 'age_months' => 30, 'status' => 'reserved', 'description' => 'Friendly dog that likes long walks.'],
            ['id' => 3, 'name' => 'Pepper', 'species' => 'bunny', 'shelter_id' => 2, 'shelter_name' => 'River Shelter', 'age_months' => 8, 'status' => 'available', 'description' => 'Young bunny comfortable with children.'],
            ['id' => 4, 'name' => 'Kiwi', 'species' => 'bird', 'shelter_id' => 2, 'shelter_name' => 'River Shelter', 'age_months' => 14, 'status' => 'available', 'description' => 'Small bird with a bright song.'],
        ];
    }
}
