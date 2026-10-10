# Route Cache

CorianderPHP 0.3.0 caches the discovered route map automatically in production. This replaces controller-cache generation.

## Development And Production

With `APP_ENV=production`, the default route cache uses `cache/routes.php`. Incoming requests check the route directory periodically; the default refresh interval is 30 seconds. New files may not become visible until the next refresh.

Outside production, the router discovers routes on each request, which makes adding or renaming a method file immediately visible.

```bash
php coriander routes:list
```

This command reads current files independently of the production cache. It does not execute handlers or build/change the cache.

## No Build Step

Do not run the removed `php coriander cache controllers`. You can clear caches through:

```bash
php coriander cache clear
```

The next request rebuilds the map when needed. Clearing is useful after deployment when you need an immediate refresh, not a required step after every development edit.

## Reliability

Cache writes use a lock and atomic replacement. Missing handler or middleware files fail closed rather than executing another route. If the cache location is unwritable, discovery falls back to current files.

Still give the PHP process appropriate cache permissions and monitor logs. Do not commit generated cache files or expose the cache directory through the web server.

For specialized bootstraps, `Router` accepts a route directory and a `RouteCache` instance. Prefer the defaults unless you have a concrete reason to change them.
