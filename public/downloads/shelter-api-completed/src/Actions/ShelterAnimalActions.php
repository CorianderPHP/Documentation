<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\ShelterApi\AnimalService;
use App\Modules\ShelterApi\ApiJson;
use App\Modules\ShelterApi\NotFoundException;
use App\Modules\ShelterApi\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/*
 * Route entrypoint for /api/shelter/animals.
 * These actions translate HTTP details into service calls and return JSON.
 * It does not build SQL or duplicate validation rules.
 */
final class ShelterAnimalActions
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
        // RequestFactory parses JSON once before routing; services receive plain data.
        return (array) $request->getParsedBody();
    }
}
