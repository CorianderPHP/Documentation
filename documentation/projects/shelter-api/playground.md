# API Playground

Use the playground to test the request and response shapes from this guide without modifying a database.

## What is safe here

The playground is a documentation tool. `GET` requests read seeded demo data. `POST`, `PATCH`, and `DELETE` return realistic success or validation responses, but they do not create, update, or delete database rows.

## What to try

- Fetch all animals.
- Filter animals by species or status.
- Fetch one animal by id.
- Send an invalid create request and inspect the `422` response.
- Send a valid create request and inspect the fake `201` response.
- Patch or delete an animal and check the `meta.persisted` value.

## How this helps

The code examples in the route, controller, validation, and error chapters are easier to understand when you can immediately see the JSON result. Use the playground as a Postman-like companion while reading the project.
