<?php
declare(strict_types=1);

namespace App\Actions;

use App\Modules\ShelterApi\AnimalService;
use App\Modules\ShelterApi\ApiJson;
use Psr\Http\Message\ResponseInterface;

final class ShelterLookupActions
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
