# Build a Shelter REST API

This guided project builds a JSON API for an animal shelter. The API exposes cats, dogs, bunnies, and birds, supports filtering, validates writes, returns consistent errors, and keeps the implementation outside `CorianderCore`.

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
- Controllers translate requests into service calls and JSON responses.
- Modules contain reusable application logic.
- Database migrations own schema creation and seed data.
- Validation is centralized so create and update routes do not duplicate field rules.

## Why this is a good API example

A REST API touches the CorianderPHP pieces developers usually need after the first website: route files, request parsing, JSON responses, database access, service modules, validation, and error formatting. The forum project explains a web app with views and permissions. This project explains an API-first feature.

## Database choice

The guide uses SQLite because it is easy to run locally and useful for learning the framework. Repositories use `SQLManager::sqlScript()` for joins and filtered queries, so the final chapters only need to explain the SQL dialect changes when you move the same schema to MySQL.

## Recommended path

Read the steps in order the first time. After that, use the search box for concrete tasks such as "route file", "filter species", "validation", "SQLite", or "JSON error".
