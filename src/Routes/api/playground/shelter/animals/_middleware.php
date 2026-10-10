<?php
use App\Middleware\NumericIdMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

return [new class implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $request->getAttribute('id') === null
            ? $handler->handle($request)
            : (new NumericIdMiddleware())->process($request, $handler);
    }
}];
