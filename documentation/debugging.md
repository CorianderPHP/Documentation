# Errors And Debugging

This page lists common problems and where to look first.

## 404 Not Found

Check:

1. The route exists.
2. The route file is required by `public/routes.php`.
3. The method matches the request method.
4. Dynamic route parameters match the pattern.

Example:

```php
$blogRoutes = PROJECT_ROOT . '/src/Routes/blog.php';
if (is_file($blogRoutes)) {
    (require $blogRoutes)($router);
}
```

## 405 Method Not Allowed

The path exists, but the HTTP method does not match.

For a form with `method="POST"`, register a POST route. For a read page, use GET.

## Forbidden On Apache Or Plesk

If a URL returns Apache `Forbidden`, check:

- document root
- `.htaccess` support
- directory permissions
- whether the host blocks direct directory access
- whether the route is being handled by Apache before PHP

The application route should reach `index.php`.

## Asset MIME Type Error

If the browser says a stylesheet has MIME type `text/html`, the CSS URL is returning an HTML page instead of the CSS file.

Check:

- `PUBLIC_URL_PREFIX`
- document root
- rewrite rules
- generated `public/assets/css/output.css`
- whether the asset path exists on the host

## Environment Not Loaded

Check that `.env` exists and contains the expected keys.

For local debugging:

```env
APP_ENV=local
APP_DEBUG=1
```

For production:

```env
APP_ENV=production
APP_DEBUG=0
```

## Database Connection Fails

Check:

- `DB_TYPE`
- host and port
- username and password
- SQLite file path
- writable SQLite directory
- PHP PDO driver availability

For SQLite, relative paths are resolved from the project context. Ensure the directory exists and is writable.

## CSRF Or Session Problems

For web forms:

```php
<?= \CorianderCore\Core\Security\Csrf::input() ?>
```

Check:

- the form uses a mutating method like POST
- session cookies are accepted
- HTTPS detection is correct behind proxies
- the route is not accidentally handled as an API route

## Header Warnings In Browser

Some security headers require a trustworthy origin. Use HTTPS or `localhost` for local development.

If a browser ignores `Cross-Origin-Opener-Policy` on plain HTTP, serve the app through HTTPS or local `localhost`.

## View Not Found

Views live under:

```structure
public/public_views/
```

Use normalized relative paths:

```php
$this->view->render('blog/show', [
    'post' => $post,
]);
```

Do not pass absolute paths or paths with `..`.

## What To Log

Log enough context to understand the failure, but not secrets.

Good context:

- route name or URL
- user ID, when available
- request ID, when available
- exception message
- operation name

Avoid logging:

- passwords
- tokens
- raw session data
- full payment or identity data
