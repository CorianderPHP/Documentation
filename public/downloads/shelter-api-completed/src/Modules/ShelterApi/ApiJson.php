<?php
declare(strict_types=1);

namespace App\Modules\ShelterApi;

use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;

/*
 * Response helper:
 * every API action returns JSON through this class so success and error
 * responses stay predictable for clients and tests.
 */
final class ApiJson
{
    public static function response(array $payload, int $status = 200): ResponseInterface
    {
        return Responses::json($payload, $status);
    }

    public static function error(string $code, string $message, int $status, array $fields = []): ResponseInterface
    {
        $error = ['code' => $code, 'message' => $message];
        if ($fields !== []) {
            $error['fields'] = $fields;
        }

        return self::response(['error' => $error], $status);
    }
}
