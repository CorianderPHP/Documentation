# Upgrade Guide

The updater replaces `CorianderCore` and `coriander`, not your application. Review routes/pages, middleware policy, templates, browser tooling, Composer mappings, and bootstrap code before deployment.

## Safe Update Routine

```workflow
Record|Run php coriander version and read the release's migration notes.
Protect|Commit app changes and back up database/environment configuration.
Preview|Run php coriander update --dry-run and inspect the managed-file plan.
Migrate on a branch|Update the framework, then adapt affected app-owned files.
Validate|Build assets, run app tests, and check authentication, writes, errors, and downloads.
Deploy|Release the PHP app and its rebuilt assets together.
```

Do not patch framework-managed files. Report defects upstream instead of maintaining changes the next update replaces.

## Upgrade From 0.4.0 To 0.4.1

**This patch includes a breaking metadata rename.** The updater preserves app-owned files, so a successful core update does not update your layouts or browser tooling. Read the [0.4.1 release notes](https://github.com/CorianderPHP/CorianderPHP/releases/tag/v0.4.1).

```workflow
Metadata reads|Rename framework metadata reads in pages/layouts from $page to $metadata. Keep $page when it represents application content.
View overrides|Replace the metadata override key page with metadata in Responses::view() calls, including custom 404 rendering.
Metadata declarations|Keep _metadata.php filenames and returned arrays unchanged. The metadata key must be an array; page is unrestricted application data.
Request URLs|Use getUri()->getPath() for path checks. A string-cast URI now includes the incoming scheme, host, and port when available.
SQL conditions|Review updateWhere()/deleteWhere() calls with null conditions: they now match SQL NULL rows instead of matching none.
Assets|Adopt nodejs/fingerprint-css.js and the released package.json build commands, preserving app scripts. Rebuild and deploy CSS plus all JavaScript entries/chunks.
Verify|Test page titles, escaped metadata, ordinary page data, authentication, absolute request URIs, nullable writes, and asset URLs.
```

An explicit view call now separates content from framework metadata:

```php
return \CorianderCore\Core\Http\Responses::view('articles/show', [
    'page' => $document,
    'metadata' => ['title' => 'Article'],
]);
```

Its header reads `<?= $metadata['title'] ?? '' ?>`. Metadata is shallow-merged before one escaping pass; object properties in `$page` remain raw. There is no automatic `$page` metadata alias.

The incoming host is client-controlled. Use a configured canonical origin for links that must use your own domain, not `(string) $request->getUri()`. Forwarded hosts are ignored; HTTPS detection still follows trusted-proxy configuration. See [Request Handlers](/documentation/handlers).

CSS/JavaScript version strings are opaque content hashes, not timestamps. Pass raw relative paths to `PublicUrl::versionedAsset()` rather than pre-encoding them. Unmarked files use content hashing; rebuilt files use bounded fingerprint reads. Other asset types retain timestamp versions. See [Assets And Images](/documentation/assets) and [NodeJS Integration](/documentation/nodejs).

Dependencies have not changed from 0.4.0. You do not need a database migration for these framework fixes; you do need to review the effect of existing nullable write conditions. See [Database Guide](/documentation/database).

## Upgrade From 0.3.0 To 0.4.1

0.4.0 introduced the shared route/view tree and new browser tooling. The steps below use the current 0.4.1 metadata names. A core-only update is not a completed migration. Read the [0.4.0 release notes](https://github.com/CorianderPHP/CorianderPHP/releases/tag/v0.4.0) and the 0.4.1 notes above.

### Move Routes And Their Protection Together

Move method files from `src/Routes` into corresponding paths under `src/Views`. Move **every** `_middleware.php` with them, including the root policy and nested admin gates.

Resolve overlapping files before moving. Use exact `src/Views` casing on Linux. Remove the old route tree only after checking the discovered URLs.

```structure
src/Routes/admin/index.get.php       -> src/Views/admin/index.get.php
src/Routes/admin/_middleware.php     -> src/Views/admin/_middleware.php
src/Routes/_middleware.php           -> src/Views/_middleware.php
```

Keep authentication, CSRF, request limits, and analytics/security headers. Directory children cannot disable a parent's middleware.

### Choose Direct Pages Or Explicit Templates

Replace trivial GET forwarding handlers with page HTML at a `.get.php` filename. A direct page has no explicit return; it receives `$request` and `$metadata` and renders with inherited layouts.

Keep JSON, redirects, and non-GET writes as request-to-response callables. A file cannot both print HTML and return a handler.

Complex pages may still return `Responses::view('articles/show', $data)` using an **unsuffixed** `show.php` template. Explicit rendering rejects method files and reserved layout/declaration names. See [Dynamic View Guide](/documentation/dynamic-views).

### Adopt Metadata Without Reusing Its Data Key

Optional `_metadata.php` files return arrays without output. Defaults merge root to leaf with shallow child overrides. Layouts read `$metadata['title']`; variables set inside a direct page are not forwarded to layouts.

Explicit rendering may override metadata with:

```php
return \CorianderCore\Core\Http\Responses::view('articles/show', [
    'metadata' => ['title' => 'Article'],
    'article' => $article,
]);
```

`metadata` must be an array. Ordinary application data may still use `page`, including a document/paginator object. Objects remain raw; encode their properties when rendering.

Metadata and explicit string-array data are escaped recursively. Raw request values and direct-page local data still require output encoding.

### Update 404 Handling And Compatibility URLs

The starter uses a private `_notfound.php` template. Update custom 404 middleware so it preserves unrelated response headers while replacing the body, and removes obsolete content framing/validators.

The starter no longer includes `/home`. Keep an explicit redirect to `/` if your existing application has bookmarks or inbound links.

A custom Router or generator targeting an old directory needs review. Custom `MakeSitemap` construction now takes a destination **directory**, not a filename.

### Migrate Browser Code And Tooling

Move paired entries beside GET pages. Put directory initialization in `_script.ts` and shared imported helpers/types in `src/Scripts`.

```structure
src/Views/about/index.get.php
src/Views/about/index.ts
src/Views/_script.ts
src/Scripts/menu.ts
public/assets/js/pages/
  about/index.js
  _script.js
  _chunks/
```

Compare and adopt the released `nodejs/package.json`, lockfile, `tsconfig.json`, `build.js`, `watch.all.js`, `fingerprint-css.js`, Tailwind configuration, and CSS source declarations. Preserve your custom theme and app scripts.

Remove old `build.min.js`/`build.watch.js` command references. Type checks now use `tsc --noEmit`; esbuild publishes JavaScript. Keep `moduleDetection: "force"` and explicit CSS `@source` declarations for Views/Scripts.

Use Node.js 24 for CI or supported Node.js 22 locally. The current lockfile pins Tailwind/CLI 4.3.0 with the patched watcher chain.

Remove manual script tags after adopting automatic loading. Explicit full views load template-directory modules; direct pages also load their paired entry. JSON, redirects, explicit HTML, and fragments remain undecorated.

### Rebuild And Deploy Complete Assets

```bash
php coriander nodejs ci
php coriander nodejs run build-prod
php coriander routes:list
composer test
```

Deploy the whole generated `public/assets/js/pages` tree, including shared `_chunks`. Keep the build-only `.generated.json` locally for safe cleanup; do not deploy it. Review old unrecorded output before removing it.

CSS/JavaScript URLs use content fingerprints, including timestamp-preserving deployments. PHP-only/prebuilt deployments need no Node.js or TypeScript sources on the server.

See [Page Scripts](/documentation/page-scripts) and [NodeJS Integration](/documentation/nodejs) for the full tooling contract.

## Earlier 0.2.x Applications

Apply the [0.3.0 framework migration](https://github.com/CorianderPHP/CorianderPHP/blob/v0.4.1/docs/migration-0.3.0.md) first, then the 0.4.1 steps above.

That earlier change removed manual route registration, controller discovery/attributes, the Container, and controller-cache builds. It introduced PSR response handlers, private Views, nearest layouts, and explicit on-demand session startup.

Do not copy old route includes or controller discovery into a 0.4.1 app.

## Before Deployment

Verify GET/HEAD, missing paths, 405/Allow, direct and explicit pages, metadata, layouts, script/chunk URLs, forms/CSRF, login/logout, admin denial, parsed JSON, request limits, and actual repository writes.

Regenerate and run downloadable examples when distributing them. Production route maps refresh automatically, normally within 30 seconds; no deploy-time route-build command is required. Keep OPcache timestamp validation enabled or restart PHP workers when replacing PHP files.
