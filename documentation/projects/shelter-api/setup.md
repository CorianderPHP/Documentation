# Shelter API Project Structure

Start from an installed CorianderPHP **0.3.0** app. This project uses ordinary App classes and discovered method files; it does not need an API controller generator or manual route registration.

## Verify Routing First

Create `src/Routes/api/shelter/health.get.php`:

```php
<?php
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    Responses::json(['ok' => true]);
```

Open /api/shelter/health and run `php coriander routes:list`. A JSON `{"ok":true}` confirms routing before you add database dependencies.

The routes chapter introduces the actual CRUD method files; do not create a duplicate api/shelter.php registration file.

## Prepare App-Owned Folders

```structure
src/
  Routes/api/shelter/
  Actions/
    ShelterAnimalActions.php
    ShelterLookupActions.php
  Modules/ShelterApi/
    AnimalRepository.php
    AnimalService.php
    AnimalValidator.php
    ApiJson.php
    NotFoundException.php
    ValidationException.php
database/migrations/
```

Create these classes as ordinary PHP files. The starter's `"App\\": "src/"` mapping makes `App\Actions\ShelterAnimalActions` and `App\Modules\ShelterApi\AnimalService` autoloadable. Run `composer dump-autoload` after changing that mapping.

Action classes coordinate HTTP input/output. Services own validation/workflows. Repositories own SQL. These app conventions do not expose URLs by themselves.

## Configure SQLite

```bash
php coriander make:database
```

Choose SQLite or edit your environment:

```env
DB_TYPE=sqlite
DB_NAME=database/shelter.sqlite
```

Create the database directory if missing and make it writable locally. The [next chapter](/guided-projects/shelter-api/data-model) adds the schema and seed rows with a migration.

## Local Stateless Writes And CSRF

The starter protects all mutating routes, including /api. This local tutorial API does not use browser login cookies. In the root `src/Routes/_middleware.php`, replace the existing CSRF entry with:

```php
new \CorianderCore\Core\Security\CsrfMiddleware(apiPrefixes: ['api/shelter'])
```

Keep security headers and request limits. Adding an exception in child middleware cannot undo a parent's CSRF check.

The download includes this root policy for a **fresh local starter**. Merge the narrow exception rather than overwriting an existing application's middleware. This exception does not secure a writable public API; add authentication, permissions, and abuse controls before deployment.

The hosted playground has a different prefix and never writes to storage. See [Security](/documentation/security) for cookie-based APIs that must not be exempted.

## Checkpoint

Health GET responds, SQLite is configured, and all app files stay outside CorianderCore. Continue with [Shelter Data Model](/guided-projects/shelter-api/data-model).
