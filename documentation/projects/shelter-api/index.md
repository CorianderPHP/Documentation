# Build a Shelter REST API

This guided project builds a JSON API for an animal shelter. The API exposes cats, dogs, bunnies, and birds, supports filtering, validates writes, returns consistent errors, and keeps the implementation outside `CorianderCore`.

Use CorianderPHP **0.3.0**. The downloadable API performs real local SQLite writes; the hosted playground returns simulated results and never changes storage. Tutorial access is deliberately local: add authentication and authorization before exposing writable endpoints publicly.

## What you will build

- `GET /api/shelter/animals` lists animals with filters for species, shelter, status, age, and search text.
- `GET /api/shelter/animals/{id}` returns one animal.
- `POST /api/shelter/animals` creates an animal.
- `PATCH /api/shelter/animals/{id}` updates an animal.
- `DELETE /api/shelter/animals/{id}` marks an animal as archived.
- `GET /api/shelter/species` lists cats, dogs, bunnies, and birds.
- `GET /api/shelter/shelters` lists shelter locations.

## Project shape

The project is intentionally split by responsibility:

- Routes only describe HTTP paths and methods.
- Request Handlers translate requests into service calls and JSON responses.
- Modules contain reusable application logic.
- Database migrations own schema creation and seed data.
- Validation is centralized so create and update routes do not duplicate field rules.

## Why this is a good API example

A REST API touches the CorianderPHP pieces developers usually need after the first website: route files, request parsing, JSON responses, database access, service modules, validation, and error formatting. The forum project explains a web app with views and permissions. This project explains an API-first feature.

## Database choice

The guide uses SQLite because it is easy to run locally and useful for learning the framework. Repositories use `SQLManager::sqlScript()` for joins and filtered queries, so the final chapters only need to explain the SQL dialect changes when you move the same schema to MySQL.

## Recommended path

Read the steps in order the first time. After that, use the search box for concrete tasks such as "route file", "filter species", "validation", "SQLite", or "JSON error".

When you are unsure where a piece belongs, place it in this API lifecycle:

```workflow
Route|Matches the HTTP method and API path.
API handler|Reads route attributes, query parameters, or JSON body data.
Service|Applies validation, workflow rules, and business decisions.
Repository|Runs SQL and returns storage data.
JSON response|`ApiJson` returns a stable response shape.
```

- Routes define the HTTP contract.
- API request handlers read request data and choose the response shape.
- Services own validation, workflow, and business decisions.
- Repositories own SQL.
- `ApiJson` keeps every response shape consistent.

The method files are discovered under `src/Routes/api/shelter`. Read parsed JSON with `getParsedBody()` and return `Responses`-based JSON responses. The root middleware policy is covered in [Project Structure](/guided-projects/shelter-api/setup).

## Download

[Download completed API](/public/downloads/shelter-api-completed.zip) for comparison or local testing. It contains app-owned files, not the framework. Follow its README to install dependencies, configure SQLite, merge middleware, and migrate.
