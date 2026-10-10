# Shelter API Completed App Files

These app-owned files require **CorianderPHP 0.3.0** and do not include the framework. Unlike the hosted playground, this API persists writes to your local SQLite database.

## Install In A Fresh Local Starter

1. Install the 0.3.0 framework, composer install, and frontend dependencies if needed.
2. Copy src and database into the starter. Keep its public/index.php.
3. Use the starter's `"App\\": "src/"` Composer mapping and run composer dump-autoload.
4. Configure DB_TYPE=sqlite and DB_NAME=database/shelter.sqlite in .env. Ensure the database directory is writable.
5. Run php coriander migrate and php coriander routes:list.
6. Request /api/shelter/animals; the migration seeds Milo, Nala, Pepper, and Kiwi.

Method files under src/Routes/api/shelter are discovered automatically. There is no public/routes.php include or API controller discovery.

The included root _middleware.php retains headers/request limits and makes a narrow api/shelter CSRF exception for a stateless **local tutorial** API. In an existing app, merge that exception into the current root policy; do not overwrite authentication or other protection. This API has no production authentication: secure writable endpoints before making them public.

## Try Real Writes

```bash
curl -H "Content-Type: application/json" -d '{"name":"Poppy","species":"bunny","shelter_id":2,"age_months":7}' http://localhost:8080/api/shelter/animals
curl -X PATCH -H "Content-Type: application/json" -d '{"status":"reserved"}' http://localhost:8080/api/shelter/animals/5
curl -X DELETE http://localhost:8080/api/shelter/animals/5
```

Use the actual id from the POST response, rather than assuming it is 5. DELETE archives the row; public reads no longer include it.

On PowerShell, use curl.exe and quote JSON for your shell. The migration user also needs write access to adjacent migration lock files.

## Request Flow

```workflow
Route|Discovered method files map HTTP methods and URLs.
Action|src/Actions reads parsed request data and returns JSON.
Service|AnimalService owns workflows and validation calls.
Repository|AnimalRepository runs SQL through SQLManager::sqlScript().
JSON response|ApiJson uses framework Responses helpers.
```

RequestFactory parses JSON before routing. Invalid JSON returns 400 and excessive bodies 413 through the starter's error boundary; business errors use the API error envelope. The emitter must receive the original method so HEAD returns no body.

## Endpoints

- GET /api/shelter/animals and /api/shelter/animals/{id}
- POST /api/shelter/animals
- PATCH or DELETE /api/shelter/animals/{id}
- GET /api/shelter/species and /api/shelter/shelters

See https://corianderphp.com/guided-projects/shelter-api for filtering, validation, deployment limits, and MySQL migration notes. Do not edit CorianderCore.
