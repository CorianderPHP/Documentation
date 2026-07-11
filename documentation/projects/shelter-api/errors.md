# Errors And Versioning

An API is easier to consume when every error has the same shape. Clients should not parse framework errors, PHP notices, or plain strings.

## Error shape

Use one shape everywhere:

```json
{
  "error": {
    "code": "not_found",
    "message": "Animal not found."
  }
}
```

For validation errors, add a `fields` object:

```json
{
  "error": {
    "code": "validation_failed",
    "message": "The request body is invalid.",
    "fields": {
      "name": ["Name is required."]
    }
  }
}
```

## Status codes

- `200` for successful reads and updates.
- `201` for created animals.
- `400` for malformed JSON.
- `404` for missing animals.
- `409` for conflicts such as updating an archived animal.
- `422` for validation failures.
- `500` for unexpected server errors.

## Centralize helpers

Extend `ApiJson` with helpers so controllers do not repeat response shapes:

```php
public static function error(string $code, string $message, int $status, array $fields = []): Response
{
    $error = ['code' => $code, 'message' => $message];
    if ($fields !== []) {
        $error['fields'] = $fields;
    }

    return self::response(['error' => $error], $status);
}
```

## Versioning

For a first internal API, `/api/shelter/...` is enough. If external clients depend on the API, introduce a versioned prefix before breaking changes:

```text
/api/v1/shelter/animals
/api/v1/shelter/species
/api/v1/shelter/shelters
```

Keep versioning in the route file. The controller and service names do not need to contain `V1` until the behavior actually diverges.

## MySQL production note

SQLite is good for this guide. For production MySQL, keep the same repository interface and change only the connection configuration and SQL dialect details from the data model step. Controllers should not know which database driver is used.
