<?php
declare(strict_types=1);

define('PROJECT_ROOT', dirname(__DIR__));
putenv('APP_ENV=testing');
putenv('APP_DEBUG=0');
putenv('DB_TYPE=sqlite');
putenv('DB_NAME=:memory:');
require_once PROJECT_ROOT . '/config/config.php';
require_once PROJECT_ROOT . '/vendor/autoload.php';
require_once PROJECT_ROOT . '/CorianderCore/autoload.php';
