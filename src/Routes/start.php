<?php
declare(strict_types=1);

use Controllers\StartController;
use CorianderCore\Core\Router\Router;

return static function (Router $router): void {
    $router->get('start', static fn () => (new StartController())->index());
};
