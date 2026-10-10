# Request Lifecycle

This page follows one request through the 0.3.0 starter. It helps identify which layer to inspect when an endpoint fails.

```workflow
Front controller|public/index.php loads configuration and the framework autoloader.
Request factory|RequestFactory::fromGlobals() bounds the body and parses query, form/JSON, cookies, and uploads.
Router|Router::handle() selects a discovered method file and assigns path parameters.
Middleware|Root-to-child middleware can return early or delegate to the next handler.
Handler and modules|The handler coordinates app logic and returns a response.
Emitter|ResponseEmitter sends headers/status and suppresses bodies for HEAD.
```

## Bootstrap API

The request-handling part of a custom `public/index.php` looks like this, after configuration and autoloading:

```php
use CorianderCore\Core\Http\ErrorResponse;
use CorianderCore\Core\Http\RequestFactory;
use CorianderCore\Core\Http\ResponseEmitter;
use CorianderCore\Core\Router\Router;

try {
    $request = RequestFactory::fromGlobals();
    $response = (new Router())->handle($request);
} catch (Throwable $exception) {
    $response = ErrorResponse::fromException($exception);
}

ResponseEmitter::emit($response, $_SERVER['REQUEST_METHOD'] ?? 'GET');
```

There is no Container setup, manual route include, or `dispatch()` call. Keep the released starter's configuration/session-cookie setup rather than copying only this excerpt into an empty file.

Pass the original HTTP method to the emitter, including error paths. HEAD requests must not receive a body.

## Parsing And Error Boundaries

The request factory rejects malformed JSON with 400 and oversized bodies with 413 before routing. Middleware cannot catch an exception thrown before the router is called; the front controller's catch handles it.

Business validation happens later in your handler/service. Valid JSON with a missing required field normally produces 422, not a parser error.

## Sessions Are Lazy

Cookie configuration does not start a session. Public documentation pages can respond without a session cookie or lock. Authentication and flash code must call `SessionBootstrap::start()` before using `$_SESSION`.

`Csrf::input()`/`Csrf::token()` and validation start the session when needed. See [Sessions](/documentation/sessions).

## Debug By Layer

- Connection refused: hosting, port, TLS, or DNS, before PHP.
- 404: route filename/path or a missing data record.
- 405: the path exists, but that method file does not.
- 403: CSRF or permission middleware rejected the request.
- 400/413: request parsing/size limits.
- 500: invalid route return, missing template, dependency, or application exception.

Use `php coriander routes:list` first for routing problems. See [Errors And Debugging](/documentation/debugging) for checks and [Security](/documentation/security) for safe production error handling.
