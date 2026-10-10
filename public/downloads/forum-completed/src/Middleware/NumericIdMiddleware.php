<?php
declare(strict_types=1);

namespace App\Middleware;

use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NumericIdMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = (string) $request->getAttribute('id');
        return ctype_digit($id) && (int) $id > 0
            ? $handler->handle($request)
            : Responses::json(['error' => ['code' => 'not_found', 'message' => 'Invalid identifier.']], 404);
    }
}
