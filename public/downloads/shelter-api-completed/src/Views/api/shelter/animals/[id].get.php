<?php
declare(strict_types=1);

use App\Actions\ShelterAnimalActions;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

// This method file is discovered automatically; reusable logic lives in App\Actions.
return static function (ServerRequestInterface $request) {
    $id = (string) $request->getAttribute('id');
    if (!ctype_digit($id) || (int) $id < 1) {
        return Responses::json(['error' => ['code' => 'not_found', 'message' => 'Animal not found.']], 404);
    }
    return (new ShelterAnimalActions())->show($request);
};
