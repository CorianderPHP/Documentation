<?php
declare(strict_types=1);

namespace ApiControllers;

use Modules\ShelterPlayground\ShelterPlaygroundData;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ShelterPlaygroundController
{
    public function __construct(private readonly ShelterPlaygroundData $data = new ShelterPlaygroundData())
    {
    }

    public function animals(ServerRequestInterface $request): ResponseInterface
    {
        $filters = $request->getQueryParams();
        return $this->json(['data' => $this->data->animals($filters)]);
    }

    public function animal(ServerRequestInterface $request): ResponseInterface
    {
        $animal = $this->data->animal((int) $request->getAttribute('id'));
        if ($animal === null) {
            return $this->json(['error' => ['code' => 'not_found', 'message' => 'Animal not found.']], 404);
        }

        return $this->json(['data' => $animal]);
    }

    public function createAnimal(ServerRequestInterface $request): ResponseInterface
    {
        $body = $this->jsonBody($request);
        $errors = $this->validate($body);
        if ($errors !== []) {
            return $this->json([
                'error' => [
                    'code' => 'validation_failed',
                    'message' => 'The request body is invalid.',
                    'fields' => $errors,
                ],
            ], 422);
        }

        return $this->json([
            'data' => [
                'id' => 99,
                'name' => trim((string) $body['name']),
                'species' => (string) $body['species'],
                'shelter_id' => (int) $body['shelter_id'],
                'age_months' => (int) ($body['age_months'] ?? 0),
                'status' => (string) ($body['status'] ?? 'available'),
                'description' => trim((string) ($body['description'] ?? '')),
                'demo_write' => true,
            ],
            'meta' => ['persisted' => false, 'message' => 'Demo write accepted. No database row was created.'],
        ], 201);
    }

    public function updateAnimal(ServerRequestInterface $request): ResponseInterface
    {
        $animal = $this->data->animal((int) $request->getAttribute('id'));
        if ($animal === null) {
            return $this->json(['error' => ['code' => 'not_found', 'message' => 'Animal not found.']], 404);
        }

        return $this->json([
            'data' => array_merge($animal, $this->jsonBody($request), ['demo_write' => true]),
            'meta' => ['persisted' => false, 'message' => 'Demo update accepted. No database row was changed.'],
        ]);
    }

    public function deleteAnimal(ServerRequestInterface $request): ResponseInterface
    {
        $animal = $this->data->animal((int) $request->getAttribute('id'));
        if ($animal === null) {
            return $this->json(['error' => ['code' => 'not_found', 'message' => 'Animal not found.']], 404);
        }

        return $this->json([
            'data' => ['deleted' => true, 'id' => (int) $request->getAttribute('id')],
            'meta' => ['persisted' => false, 'message' => 'Demo delete accepted. No database row was deleted.'],
        ]);
    }

    public function species(): ResponseInterface
    {
        return $this->json(['data' => $this->data->species()]);
    }

    public function shelters(): ResponseInterface
    {
        return $this->json(['data' => $this->data->shelters()]);
    }

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'application/json; charset=utf-8'],
            json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        );
    }

    private function jsonBody(ServerRequestInterface $request): array
    {
        $body = json_decode((string) $request->getBody(), true);
        return is_array($body) ? $body : [];
    }

    private function validate(array $body): array
    {
        $errors = [];
        if (trim((string) ($body['name'] ?? '')) === '') {
            $errors['name'][] = 'Name is required.';
        }

        if (!in_array((string) ($body['species'] ?? ''), ['cat', 'dog', 'bunny', 'bird'], true)) {
            $errors['species'][] = 'Species must be cat, dog, bunny, or bird.';
        }

        if ((int) ($body['shelter_id'] ?? 0) < 1) {
            $errors['shelter_id'][] = 'Shelter is required.';
        }

        return $errors;
    }
}
