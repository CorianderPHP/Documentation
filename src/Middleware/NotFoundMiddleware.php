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
                return $this->replaceBody($response, $json);
            }
            return $response;
        }
        if ($response->getStatusCode() !== 404) {
            return $response;
        }
        $page = (new SiteView())->response('notfound', [], 404);
        return $this->replaceBody($response, $page);
    }

    private function replaceBody(ResponseInterface $response, ResponseInterface $replacement): ResponseInterface
    {
        $response = $response->withBody($replacement->getBody())->withHeader('Content-Type', $replacement->getHeaderLine('Content-Type'));
        foreach (['Content-Length', 'Transfer-Encoding', 'Content-Encoding', 'Content-Range', 'ETag', 'Last-Modified'] as $header) {
            $response = $response->withoutHeader($header);
        }
        return $response;
    }
}
