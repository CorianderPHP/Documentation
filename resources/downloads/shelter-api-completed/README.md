# Shelter API Completed App Files

These files are app-owned CorianderPHP project files. They do not include the CorianderPHP framework.

## Install

1. Create or open a CorianderPHP project.
2. Configure SQLite:

```env
DB_TYPE=sqlite
DB_NAME=database/shelter.sqlite
```

3. Copy the folders from this package into the project root.
4. Include `src/Routes/api/shelter.php` from `public/routes.php`. A ready-to-copy snippet is in `public/routes.snippet.php`.
5. Run:

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
