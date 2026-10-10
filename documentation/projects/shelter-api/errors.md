# Errors And Versioning

An API is easier to consume when every error has the same shape. Clients should not parse framework errors, PHP notices, or plain strings.

You are here in the flow:

```workflow
Known failure|A handler or service detects not found, validation, conflict, or malformed input.
ApiJson::error()|The helper builds one consistent error payload.
Client response|The client receives a stable JSON error with the correct HTTP status.
```

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
- `413` for a request body exceeding configured limits.
- `422` for validation failures.
- `500` for unexpected server errors.

## Centralize helpers

Extend `ApiJson` with helpers so request handlers do not repeat response shapes:

```php
public static function error(string $code, string $message, int $status, array $fields = []): ResponseInterface
{
    $error = ['code' => $code, 'message' => $message];
    if ($fields !== []) {
        $error['fields'] = $fields;
    }

    return self::response(['error' => $error], $status);
}
```

The handlers in the previous chapter catch `ValidationException` and `NotFoundException` and use this helper. Malformed JSON and size-limit errors occur earlier, in `RequestFactory`: the starter's `ErrorResponse` returns plain-text/HTML 400 or 413. If clients require the same JSON envelope for those failures, format them in the front controller's catch, before emitting. Route middleware cannot catch a failure that occurred before routing.

## Versioning

For a first internal API, `/api/shelter/...` is enough. If external clients depend on the API, introduce a versioned prefix before breaking changes:

```text
/api/v1/shelter/animals
/api/v1/shelter/species
/api/v1/shelter/shelters
```

Keep versioning in the route directory, for example `src/Routes/api/v1/shelter`. The handler and service names do not need to contain `V1` until the behavior actually diverges. Update any narrowly scoped CSRF exemption when changing that prefix; secure public writes separately.

The completed implementation treats archived animals as not found. If you later distinguish an edit conflict from a missing record, define that rule and return 409 explicitly; it is not automatic framework behavior.

## MySQL production note

SQLite is good for this guide. For production MySQL, keep the same repository interface and change only the connection configuration and SQL dialect details from the data model step. Request Handlers should not know which database driver is used.

## Checkpoint

Request a missing animal:

```http
GET /api/shelter/animals/999999
```

You should receive `404` and the same top-level `error` shape shown above. If you receive plain text or a PHP error, centralize that path through `ApiJson::error()`.
