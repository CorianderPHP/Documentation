<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Modules\Docs\SiteView;
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotFoundMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (str_starts_with($request->getUri()->getPath(), '/api/')) {
            if ($response->getStatusCode() >= 400 && !str_contains($response->getHeaderLine('Content-Type'), 'application/json')) {
                $json = Responses::json(['ok' => false, 'message' => (string) $response->getBody()], $response->getStatusCode());
                return $response->hasHeader('Allow') ? $json->withHeader('Allow', $response->getHeader('Allow')) : $json;
            }
            return $response;
        }
        return $response->getStatusCode() === 404
            ? (new SiteView())->response('notfound', [], 404)
            : $response;
    }
}
