<?php
declare(strict_types=1);

use CorianderCore\Core\Router\Router;

return static function (Router $router): void {
    $shelterRoutes = PROJECT_ROOT . '/src/Routes/api/shelter.php';
    if (is_file($shelterRoutes)) {
        (require $shelterRoutes)($router);
    }
};
