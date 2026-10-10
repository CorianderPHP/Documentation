# Errors And Debugging

First identify whether the request reaches PHP, the router, middleware, or the handler. Do not disable protection to make an error disappear.

## No Connection Or Host 404

Connection refused, TLS failures, or a host-generated "site not configured" page happen before application routing. Check DNS, virtual-host bindings, HTTPS certificates, firewall/ports, and the configured document root.

The preferred document root is `public/` with `PUBLIC_URL_PREFIX=`. See [Production Checklist](/documentation/production).

## Route 404 Or 405

```bash
php coriander routes:list
```

Look for the exact path and method. `src/Routes/about.get.php` means GET /about; a view or action class alone is not a route. `index.get.php` represents its directory's root.

404 means no path or a missing application record. 405 means a path exists but does not accept that method; inspect the `Allow` header. Static paths take precedence over dynamic matches.

There is no route include or controller cache to rebuild. In production, allow the automatic route-map refresh interval or clear the cache after deployment.

## Missing View Or Invalid Return

A handler must return a PSR response:

```php
return \CorianderCore\Core\Http\Responses::view('articles/show', [
    'title' => 'Article',
    'article' => $article,
]);
```

The template is `src/Views/articles/show.php`, not an `index.php` folder. Do not echo the result or return null. Check nearest `_header.php`/`_footer.php` when the page frame is missing or duplicated.

If printed text appears before the response, move output into a template or `Responses::html()`.

## CSRF 403 Or Missing Login State

Confirm root CSRF middleware, a body field named `csrf_token`, and the session cookie. The built-in middleware does not read a token header and does not automatically exempt /api.

Call `SessionBootstrap::start()` before authentication or flash code reads `$_SESSION`. Do not exempt a cookie-authenticated API to work around a token failure.

## Malformed JSON Or Oversized Upload

The request factory returns 400 for malformed JSON and 413 for oversized bodies. Send the correct Content-Type and valid JSON. Use `getParsedBody()` in the handler rather than silently turning bad JSON into an empty array.

Check PHP/web-server upload limits too. Validate uploaded-file errors and content before moving a file.

## Assets Return HTML

A stylesheet with HTML MIME type usually received a 404 page. Verify built asset files, public prefix, rewrites, and document root:

```bash
php coriander nodejs run build-prod
```

Use [PublicUrl](/documentation/assets) so URLs fit both supported hosting layouts.

## Database Errors

Check driver extensions, environment connection values, writable SQLite parent directories, and migration status:

```bash
php coriander migrate:status
```

Keep queries parameterized and inspect server logs. Do not print connection secrets or raw database exceptions to users.

## Local Debugging

Use `APP_ENV=local` or `development` and `APP_DEBUG=1` only on a trusted development machine. `ErrorResponse` then shows escaped diagnostic details. Production returns generic errors even if debug is mistakenly enabled.

Keep production PHP `display_errors=0`. Debug traces can reveal paths and business data, so never publish them.
