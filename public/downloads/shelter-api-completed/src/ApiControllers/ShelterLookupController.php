<?php
declare(strict_types=1);

namespace ApiControllers;

use Modules\ShelterApi\AnimalService;
use Modules\ShelterApi\ApiJson;
use Psr\Http\Message\ResponseInterface;

final class ShelterLookupController
{
    public function species(): ResponseInterface
    {
        return ApiJson::response(['data' => (new AnimalService())->species()]);
    }

    public function shelters(): ResponseInterface
    {
        return ApiJson::response(['data' => (new AnimalService())->shelters()]);
    }
}
