<?php
declare(strict_types=1);

namespace Modules\ShelterApi;

use Nyholm\Psr7\Response;

final class ApiJson
{
    public static function response(array $payload, int $status = 200): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'application/json; charset=utf-8'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    public static function error(string $code, string $message, int $status, array $fields = []): Response
    {
        $error = ['code' => $code, 'message' => $message];
        if ($fields !== []) {
            $error['fields'] = $fields;
        }

        return self::response(['error' => $error], $status);
    }
}
