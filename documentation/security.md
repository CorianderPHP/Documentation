# Security Guide

Security middleware is declared in `src/Routes/_middleware.php`. Keep framework protection and add app-specific authorization in child directories or services.

## CSRF: Web And Cookie-Based APIs

By default, `CsrfMiddleware` validates POST, PUT, PATCH, and DELETE on **all paths, including /api**. The old implicit API exemption is gone.

Include a token in web forms:

```html
<form method="POST" action="/articles">
    <?= \CorianderCore\Core\Security\Csrf::input() ?>
    <input name="title">
    <button type="submit">Save</button>
</form>
```

For JSON requests authenticated by a browser session cookie, send the same token in the **body**:

```json
{
  "csrf_token": "token-from-the-current-session",
  "title": "My article"
}
```

Send the session cookie too. The built-in middleware does not read an `X-CSRF-Token` header. Token generation/validation starts the session when needed; application auth still needs explicit session startup.

## Stateless API Exceptions

Only exempt a prefix when it does not rely on browser login cookies:

```php
use CorianderCore\Core\Security\CsrfMiddleware;

// In the root _middleware.php array, replace the existing CSRF entry.
new CsrfMiddleware(apiPrefixes: ['api/shelter'])
```

This is a CSRF exception, **not authentication or authorization**. A deployed writable API needs suitable authentication (for example validated bearer tokens), permission checks, and rate limiting. Never broadly exempt all `api` routes when any use a session cookie.

The public [shelter playground](/guided-projects/shelter-api/playground) is stateless and never persists visitor writes. The [forum API](/guided-projects/forum/api) uses login cookies and remains CSRF-protected.

## Input And Body Limits

The request factory bounds the body before JSON parsing and rejects malformed JSON with 400 or oversized data with 413. Read `getParsedBody()` instead of parsing again.

The starter applies `ApiRequestLimitsMiddleware(apiPrefixes: [])` to all routes. `API_MAX_BODY_BYTES` defaults to 1048576 bytes; `API_TIMEOUT_SECONDS` defaults to 15. Configure both PHP/web-server upload limits and application limits when accepting uploads.

Validate field types, lengths, allowed values, identifiers, and uploaded content. A parsed request is not trusted input.

## Response Headers

Keep `SecurityHeadersMiddleware` in root middleware. It supplies CSP, nosniff, frame/referrer policies, cross-origin policies, and HTTPS HSTS behavior.

For an external script, allow only its required host:

```php
use CorianderCore\Core\Security\SecurityHeadersMiddleware;

new SecurityHeadersMiddleware([
    'Content-Security-Policy' => "default-src 'self'; script-src 'self' https://analytics.example.com; connect-src 'self' https://analytics.example.com; base-uri 'self'; frame-ancestors 'none'; object-src 'none'",
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
])
```

Custom header arrays replace the defaults: preserve the other baseline headers you need, rather than accidentally dropping them. Do not use a wildcard to work around a blocked resource.

## Route And Template Safety

Only method files expose endpoints; views and action classes do not. Use directory middleware for protected areas, including all write handlers. Filenames with private prefixes are not routes.

View paths must be normalized relative names under `src/Views`. Do not build them from raw request values. The renderer escapes string data recursively in arrays; objects, URL validation, and JavaScript/CSS contexts need your own care.

## Cookies, Proxies, And Errors

Use HTTPS in production. `TRUSTED_PROXIES` accepts trusted IPs/CIDRs; forwarded TLS headers are trusted only for matching peers. Do not trust every proxy just to fix cookie settings.

Use `APP_ENV=production` and `APP_DEBUG=0`. `ErrorResponse` exposes detailed traces only for local/development with debug enabled; elsewhere it returns generic errors and logs unexpected exceptions. Keep PHP `display_errors=0` on the server.

## Database And Framework Updates

Use bound parameters with `SQLManager::sqlScript()`, or safe map-based helpers such as `findWhere`. Keep migrations and backups outside public access.

The updater validates sources/archive paths and supports policy restrictions. Keep secrets, logs, source files, and backups private. See [Production Checklist](/documentation/production) and [Upgrade Guide](/documentation/upgrades).
