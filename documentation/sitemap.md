# Sitemap

A sitemap lists public URLs for search engines. In 0.3.0, routes and templates do not automatically add URLs; metadata-file scanning is removed.

## Create A Sitemap Route

You can use:

```bash
php coriander make:sitemap
```

Or create `src/Routes/sitemap.xml.get.php`:

```php
<?php
declare(strict_types=1);

use CorianderCore\Core\Sitemap\SitemapHandler;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request) {
    $sitemap = new SitemapHandler();
    $sitemap->addDynamicPage('https://example.com/', 1.0);
    $sitemap->addDynamicPage('https://example.com/about', 0.5);

    return new Response(
        200,
        ['Content-Type' => 'application/xml; charset=utf-8'],
        $sitemap->toXml()
    );
};
```

Replace `https://example.com` with your public site URL. Although the method is named `addDynamicPage()`, it accepts fixed URLs too. Add public article URLs from a repository when needed.

## Inclusion Rules

Do not include login, admin, draft, or private pages. A route being reachable does not imply it belongs in the sitemap. Use an accurate last-modified date when passing the optional third argument.

`generateSitemap()` can instead write a static `public/sitemap.xml` file. Choose either a generated static file or a route for that path, not competing sources.
