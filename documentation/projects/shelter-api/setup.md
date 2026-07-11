# Project Structure

This project stays inside app-owned folders. Do not place controllers, routes, modules, migrations, SQL scripts, or assets inside `CorianderCore`, because framework updates own that directory.

## Create app files with the CLI

Use framework generators for the route file and controllers:

```bash
php coriander make:route api/shelter
php coriander make:controller ShelterAnimal --api
php coriander make:controller ShelterLookup --api
```

The route command creates `src/Routes/api/shelter.php`. The controller commands create files under `src/ApiControllers`. Keep the generated files and then replace their bodies with the project code in the route and controller chapters.

## Register the route file

Open `public/routes.php` and include the generated route file:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Router\Router;

return static function (Router $router): void {
    $shelterRoutes = PROJECT_ROOT . '/src/Routes/api/shelter.php';
    if (is_file($shelterRoutes)) {
        (require $shelterRoutes)($router);
    }
};
```

## Create app folders

Create these app-owned folders:

```structure
src/
  ApiControllers/
    ShelterAnimalController.php
    ShelterLookupController.php
  Modules/
    ShelterApi/
      AnimalRepository.php
      AnimalService.php
      AnimalValidator.php
      ApiJson.php
      ShelterDatabase.php
database/
  migrations/
```

`ApiControllers` keeps the JSON controllers separate from page controllers. `Modules/ShelterApi` holds reusable project logic. `database/migrations` contains schema changes that can be applied by the framework migration command.

## Configure SQLite

Generate a database configuration:

```bash
php coriander make:database
```

Choose SQLite and point it to an app-owned database file:

```env
DB_TYPE=sqlite
DB_NAME=database/shelter.sqlite
```

Create the folder if it does not exist:

```bash
mkdir database
```

SQLite can create the file on first connection, but the folder must exist.

## First checkpoint

Before adding database logic, register one temporary route:

```php
$router->get('/api/shelter/health', static fn () => new \Nyholm\Psr7\Response(
    200,
    ['Content-Type' => 'application/json'],
    json_encode(['ok' => true], JSON_THROW_ON_ERROR)
));
```

Visit `/api/shelter/health`. If it returns `{"ok":true}`, routing is working and the next step can focus on the database.
