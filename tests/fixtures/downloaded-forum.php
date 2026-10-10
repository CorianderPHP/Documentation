<?php
declare(strict_types=1);

// Simulate a fresh app root: package classes/templates must not resolve to the website.
$website = dirname(__DIR__, 2);
define('PROJECT_ROOT', $website . '/public/downloads/forum-completed');
putenv('APP_ENV=testing');
putenv('APP_DEBUG=0');
putenv('PUBLIC_URL_PREFIX=');
require $website . '/vendor/autoload.php';
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = PROJECT_ROOT . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
}, true, true);

use CorianderCore\Core\Router\Router;
use CorianderCore\Core\Security\Csrf;
use CorianderCore\Core\Http\RequestFactory;
use CorianderCore\Core\Bootstrap\SessionBootstrap;

SessionBootstrap::configure(false);
$router = new Router();
$request = static fn (string $method, string $path, array $data = []) => RequestFactory::fromGlobals(
    serverParams: ['REQUEST_METHOD' => $method, 'REQUEST_URI' => $path],
    postParams: [],
    headers: ['Content-Type' => 'application/json'],
    rawBody: json_encode($data, JSON_THROW_ON_ERROR),
    cookieParams: [],
    files: []
);
$guest = $router->handle($request('GET', '/forum-demo/topics/1'));
$guestAdmin = $router->handle($request('GET', '/forum-demo/admin'));
$blocked = $router->handle($request('POST', '/api/forum-demo/topic', ['title' => 'Unsaved topic']));
$token = Csrf::token();
$login = $router->handle($request('POST', '/forum-demo/login', ['quick_role' => 'admin', 'csrf_token' => $token]));
$admin = $router->handle($request('GET', '/forum-demo/admin'));
$write = $router->handle($request('POST', '/api/forum-demo/topic', ['title' => 'Unsaved topic', 'csrf_token' => $token]));
$topics = $router->handle($request('GET', '/forum-demo/topics'));
$moderation = $router->handle($request('POST', '/forum-demo/admin/topics', [
    'topic_id' => 1, 'title' => 'How do I create my first view?',
    'action' => 'lock', 'return_to' => 'topic', 'csrf_token' => $token,
]));
$topic = $router->handle($request('GET', '/forum-demo/topics/1'));
$flashConsumed = $router->handle($request('GET', '/forum-demo/topics/1'));
session_destroy();
echo json_encode([
    'guest' => $guest->getStatusCode(),
    'guideLink' => str_contains((string) $guest->getBody(), 'https://corianderphp.com/guided-projects/forum'),
    'guestAdmin' => $guestAdmin->getHeaderLine('Location'),
    'blocked' => $blocked->getStatusCode(),
    'login' => $login->getHeaderLine('Location'),
    'admin' => $admin->getStatusCode(),
    'write' => json_decode((string) $write->getBody(), true, 512, JSON_THROW_ON_ERROR),
    'persisted' => str_contains((string) $topics->getBody(), 'Unsaved topic'),
    'moderationReturn' => $moderation->getHeaderLine('Location'),
    'flashShown' => str_contains((string) $topic->getBody(), 'action passed validation but was not saved'),
    'flashRepeated' => str_contains((string) $flashConsumed->getBody(), 'action passed validation but was not saved'),
], JSON_THROW_ON_ERROR);
