# NodeJS Integration Guide

CorianderPHP's optional Node.js toolchain type-checks TypeScript, bundles browser modules with esbuild, and builds Tailwind CSS. All commands run from the project root.

## Requirements And Installation

Use Node.js 24 for CI; Node.js 22 is also supported locally. Install Node.js with npm, then install the project's locked dependencies:

```bash
php coriander nodejs ci
```

For a new project or when adding packages:

```bash
php coriander nodejs install
php coriander nodejs install axios
```

The wrapper runs npm in `nodejs`. Do not include another `npm` word in the command. Check its exit status in CI.

## Source And Output

```structure
nodejs/
  package.json
  package-lock.json
  tsconfig.json
  build.js
  watch.all.js
  tailwind.config.js
src/
  Views/                    # Pages and colocated browser entries
  Scripts/                  # Ordinary imported helpers and types
public/assets/
  css/input.css
  css/output.css
  js/pages/                 # Generated entries and shared _chunks
```

Keep npm dependencies/configuration in `nodejs`, not browser source. The [Page Scripts Guide](/documentation/page-scripts) shows a complete interaction, directory inheritance, and shared imports.

## Daily Commands

```bash
php coriander nodejs run check-types
php coriander nodejs run watch-all
php coriander nodejs run build-prod
```

`check-types` uses `tsc --noEmit`. `build-prod` checks types, bundles/minifies with esbuild, and builds minified Tailwind CSS. TypeScript does not publish JavaScript itself.

`watch-all` keeps the TypeScript and Tailwind watchers running. Stop it with Ctrl+C. New/renamed/deleted entries and shared-source changes rebuild the output. Type errors preserve the last good bundles; fixing and saving resumes builds.

For one part of the toolchain:

```bash
php coriander nodejs run watch-ts
php coriander nodejs run watch-tw
php coriander nodejs run build-ts
php coriander nodejs run build-tw
php coriander nodejs run build-all
```

Script availability depends on your app-owned `package.json`. The commands above are provided by the 0.4.0 starter.

## Tailwind Source Discovery

The 0.4.0 input CSS explicitly scans Views and Scripts:

```text
@source "../../../src/Views";
@source "../../../src/Scripts";
```

These paths are relative to `public/assets/css/input.css`. Add another `@source` when application code outside those folders contains utility classes. Preserve your custom theme when adopting updated starter tooling.

The released lockfile pins Tailwind and its CLI to 4.3.0 with the patched watcher chain. Review dependency changes and run a clean install/audit rather than blindly replacing a customized lockfile.

## Deploy Prebuilt Assets

Deploy CSS and the **entire** `public/assets/js/pages` directory, including `_chunks`. PHP inserts existing modules automatically; missing bundles are omitted rather than built on demand.

Node.js, node_modules, and TypeScript sources are unnecessary on a server serving prebuilt assets. Keep your PHP method files, layouts, and metadata.

Generated files include a content fingerprint used in script URLs. The build-only `.generated.json` list tracks cleanup ownership: keep it locally between builds, ignore it in Git, and omit it from deployment. Do not delete handwritten JavaScript when removing obsolete unrecorded output.

## Windows npm Resolution

Verify the intended Node/npm installation:

```powershell
where.exe node
where.exe npm
Get-Command npm -All
```

For a custom installation, explicitly select the launcher:

```powershell
$env:CORIANDER_NPM_EXECUTABLE = "C:\Program Files\nodejs\npm.cmd"
php coriander nodejs install
```

`NPM_EXECUTABLE` is also supported. Do not configure npm's internal JavaScript files as a substitute for a working launcher.

## Updating An Older App

The core updater does not migrate asset tooling. Compare the released `build.js`, `watch.all.js`, TypeScript configuration, package/lock files, Tailwind configuration, and CSS source declarations with your application.

Move browser code from `nodejs/src` to the view tree/shared Scripts folder, update imports, remove old `build.min.js`/`build.watch.js` commands, and remove duplicate manual script tags. Follow [Upgrade Guide](/documentation/upgrades) before deployment.
