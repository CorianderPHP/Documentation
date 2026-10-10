<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once PROJECT_ROOT . '/CorianderCore/autoload.php';

use CorianderCore\Core\Bootstrap\SessionBootstrap;
use CorianderCore\Core\Bootstrap\TimezoneBootstrap;
use CorianderCore\Core\Http\ErrorResponse;
use CorianderCore\Core\Http\RequestFactory;
use CorianderCore\Core\Http\ResponseEmitter;
use CorianderCore\Core\Http\TrustedProxy;
use CorianderCore\Core\Router\Router;
use CorianderCore\Core\Router\SafePath;

// Only public assets/download archives bypass the development router.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_string($path) && preg_match('~^/(?:public/)?(assets/[^?]+\.(?:css|js|png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf)|downloads/[^/]+\.zip)$~i', $path, $match)) {
        try {
            SafePath::resolveFile(__DIR__, $match[1]);
            return false;
        } catch (RuntimeException) {
        }
    }
}

try {
    SessionBootstrap::configure(TrustedProxy::isSecureRequest($_SERVER));
    $timezone = getenv('APP_TIMEZONE');
    TimezoneBootstrap::applyFromEnvironment(is_string($timezone) ? $timezone : null);
    $request = RequestFactory::fromGlobals();
    $response = (new Router())->handle($request);
} catch (Throwable $exception) {
    $response = ErrorResponse::fromException($exception);
}
ResponseEmitter::emit($response, $_SERVER['REQUEST_METHOD'] ?? 'GET');
