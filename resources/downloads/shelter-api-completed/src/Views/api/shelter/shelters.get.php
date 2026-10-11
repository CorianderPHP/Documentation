<?php
declare(strict_types=1);

use App\Actions\ShelterLookupActions;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

// This method file is discovered automatically; reusable logic lives in App\Actions.
return static function (ServerRequestInterface $request) {
    return (new ShelterLookupActions())->shelters($request);
};
