<?php
declare(strict_types=1);

use CorianderCore\Core\Router\Router;

/*
 * Copy this snippet into public/routes.php when installing the completed API
 * files. It loads the app-owned route file without modifying CorianderCore.
 */
return static function (Router $router): void {
    $shelterRoutes = PROJECT_ROOT . '/src/Routes/api/shelter.php';
    if (is_file($shelterRoutes)) {
        (require $shelterRoutes)($router);
    }
};
