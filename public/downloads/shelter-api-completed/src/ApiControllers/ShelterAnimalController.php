<?php
declare(strict_types=1);

namespace ApiControllers;

use Modules\ShelterApi\AnimalService;
use Modules\ShelterApi\ApiJson;
use Modules\ShelterApi\NotFoundException;
use Modules\ShelterApi\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ShelterAnimalController
{
    public function __construct(private readonly AnimalService $animals = new AnimalService())
    {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return ApiJson::response(['data' => $this->animals->list($request->getQueryParams())]);
    }

    public function show(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return ApiJson::response(['data' => $this->animals->find((int) $request->getAttribute('id'))]);
        } catch (NotFoundException $exception) {
            return ApiJson::error('not_found', $exception->getMessage(), 404);
        }
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return ApiJson::response(['data' => $this->animals->create($this->jsonBody($request))], 201);
        } catch (ValidationException $exception) {
            return ApiJson::error('validation_failed', 'The request body is invalid.', 422, $exception->fields());
        }
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return ApiJson::response(['data' => $this->animals->update((int) $request->getAttribute('id'), $this->jsonBody($request))]);
        } catch (NotFoundException $exception) {
            return ApiJson::error('not_found', $exception->getMessage(), 404);
        } catch (ValidationException $exception) {
            return ApiJson::error('validation_failed', 'The request body is invalid.', 422, $exception->fields());
        }
    }

    public function destroy(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->animals->archive((int) $request->getAttribute('id'));
            return ApiJson::response(['data' => ['deleted' => true]]);
        } catch (NotFoundException $exception) {
            return ApiJson::error('not_found', $exception->getMessage(), 404);
        }
    }

    private function jsonBody(ServerRequestInterface $request): array
    {
        $body = json_decode((string) $request->getBody(), true);
        return is_array($body) ? $body : [];
    }
}
