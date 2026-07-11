<?php
declare(strict_types=1);

namespace Modules\ShelterApi;

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
