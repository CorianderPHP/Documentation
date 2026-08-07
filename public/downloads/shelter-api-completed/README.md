# Shelter API Completed App Files

These files are app-owned CorianderPHP project files. They do not include the CorianderPHP framework.

## Request Flow

```workflow
Route|`src/Routes/api/shelter.php` maps HTTP methods and URLs.
API controller|`src/ApiControllers` parse requests and return JSON.
Service|`src/Modules/ShelterApi/AnimalService.php` owns workflow and validation calls.
Repository|`src/Modules/ShelterApi/AnimalRepository.php` owns SQL.
JSON response|`src/Modules/ShelterApi/ApiJson.php` keeps response shapes consistent.
```

- `src/Routes/api/shelter.php` maps HTTP methods and URLs.
- `src/ApiControllers` parse requests and return JSON.
- `src/Modules/ShelterApi/AnimalService.php` owns workflow and validation calls.
- `src/Modules/ShelterApi/AnimalRepository.php` owns SQL.
- `src/Modules/ShelterApi/ApiJson.php` keeps response shapes consistent.

## Install

```workflow
Project|Create or open a CorianderPHP project.
SQLite|Configure SQLite in `.env`.
Copy files|Copy the folders from this package into the project root.
Routes|Include `src/Routes/api/shelter.php` from `public/routes.php`; a ready-to-copy snippet is in `public/routes.snippet.php`.
Migrate|Run `php coriander migrate`.
```

SQLite configuration:

```env
DB_TYPE=sqlite
DB_NAME=database/shelter.sqlite
```

```bash
php coriander migrate
```

## Endpoints

- `GET /api/shelter/animals`
- `GET /api/shelter/animals/{id}`
- `POST /api/shelter/animals`
- `PATCH /api/shelter/animals/{id}`
- `DELETE /api/shelter/animals/{id}`
- `GET /api/shelter/species`
- `GET /api/shelter/shelters`
