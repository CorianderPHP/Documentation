<?php
// Set the language attribute for the <html> tag on the home page.
$lang = isset($lang) ? $lang : 'en';

// SEO metadata: Title and meta tags for the home page.
$metadata = isset($metadata) ? $metadata : '
<title>CorianderPHP - Lightweight PHP Framework Documentation</title>
<meta name="description" content="Official CorianderPHP documentation with searchable guides and a live forum permissions project.">
';

// Include this page in the sitemap for SEO purposes.
$addViewInSitemap = isset($addViewInSitemap) ? $addViewInSitemap : true;

// Set sitemap priority for this page (0.0 - lowest, 1.0 - highest).
$sitemapPriority = isset($sitemapPriority) ? $sitemapPriority : 1.0;
