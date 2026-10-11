# Page Scripts And Shared TypeScript

Node.js is optional at runtime. Build TypeScript locally or in CI, then deploy the compiled JavaScript. PHP does not run Node.js or compile sources for visitors.

## Add A Working Interaction

Create a page at `src/Views/counter/index.get.php`:

```html
<section>
    <h1>Counter</h1>
    <p data-count aria-live="polite">0</p>
    <button type="button" data-increment>Increment</button>
</section>
```

Create `src/Views/counter/index.ts` beside it:

```typescript
const button = document.querySelector<HTMLButtonElement>('[data-increment]');
const output = document.querySelector<HTMLElement>('[data-count]');
let count = 0;

button?.addEventListener('click', () => {
    count += 1;
    if (output) output.textContent = String(count);
});
```

Build from the project root:

```bash
php coriander nodejs install
php coriander nodejs run build-prod
```

Visit `/counter`. The framework includes `public/assets/js/pages/counter/index.js` automatically. Do not add a manual duplicate tag. If the interaction does nothing, verify the bundle exists and the browser received it.

## How Entry Discovery Works

```structure
src/
  Views/
    _script.ts                   # Site-wide module
    index.get.php
    index.ts                     # Paired homepage module
    users/
      _script.ts                 # Module for this directory
      [id].get.php
      [id].ts                    # One bundle shared by all user IDs
      [id].post.php              # Not a browser entry
  Scripts/
    menu.ts                      # Shared imported code
    types.ts                     # Shared imported types
public/assets/js/pages/
  _script.js
  index.js
  users/
    _script.js
    [id].js
  _chunks/                       # Generated shared imports
```

`index.ts` pairs with `index.get.php`; `[id].ts` pairs with `[id].get.php`. TypeScript without a paired GET file is only imported code, except `_script.ts`, which is a directory entry. Private names/directories beginning with `_` or `.` are excluded from entry discovery except directory `_script.ts`.

Discovery inspects filenames without executing PHP pages or metadata. Different plain entries may reuse local variable names because `moduleDetection: "force"` gives them independent module scopes.

## Directory-Wide Initialization

For `users/[id].get.php`, existing bundles load in this order:

```workflow
Root|pages/_script.js initializes site-wide behavior.
Directory|pages/users/_script.js initializes behavior for users pages.
Page|pages/users/[id].js initializes this page.
```

Modules appear once, after page HTML and before the footer. Missing bundles are omitted. Tags use `type="module"`; asynchronous work should coordinate through imports/promises, not assumed timing.

All parameter values reuse the same dynamic filename. The URL encodes brackets and respects `PUBLIC_URL_PREFIX`. Do not generate a script per database record.

## Import Shared Helpers

Create only helpers you actually need. These are application examples, not framework-provided classes.

`src/Scripts/types.ts`:

```typescript
export type MenuOptions = { selector: string };
```

`src/Scripts/menu.ts`:

```typescript
import type { MenuOptions } from './types';

export function initializeMenu(options: MenuOptions): void {
    document.querySelector<HTMLButtonElement>(options.selector)
        ?.addEventListener('click', event => {
            const button = event.currentTarget as HTMLButtonElement;
            button.setAttribute('aria-expanded',
                String(button.getAttribute('aria-expanded') !== 'true'));
        });
}
```

`src/Views/about/index.ts`:

```typescript
import { initializeMenu } from '../../Scripts/menu';
import type { MenuOptions } from '../../Scripts/types';

initializeMenu({ selector: '[data-menu-toggle]' } satisfies MenuOptions);
```

The corresponding GET page must contain that button. This helper only toggles its attribute; your real menu must also update visibility/focus as required.

Shared imports produce ESM modules under `_chunks` and keep one module instance across directory/page entries on a rendered page. Type-only imports emit no browser code. Files in `src/Scripts` never load automatically.

Use explicit exports/imports for shared state. Use `declare global` inside a module only for deliberate ambient declarations, not accidental globals.

## Explicit Views And Responses

Direct GET pages load directory modules and their paired page module. `Responses::view('articles/show', ...)` loads directory modules for the unsuffixed template, but no paired page module. Put its initialization in that directory's `_script.ts`.

`layout: false` skips scripts as well as layouts. Returned JSON, redirects, and explicit HTML remain unchanged. A custom `ViewRenderer` root can supply its matching compiled pages directory as the second constructor argument; URLs still use `assets/js/pages`.

## Builds, Watchers, And Deployment

```bash
php coriander nodejs run check-types
php coriander nodejs run watch-all
php coriander nodejs run build-prod
```

Type checks use `tsc --noEmit`; esbuild writes the modules. Watch detects additions, renames, removals, shared-source edits, and TypeScript configuration changes. Failed checks preserve the last good output; fix the error and save to rebuild.

Deploy **all of `public/assets/js/pages`, including `_chunks`**, plus the CSS. Prebuilt deployments need neither Node.js nor TypeScript source files on the server.

Generated scripts and production CSS carry content fingerprints, so cache URLs change even if timestamps do not. PHP reads a bounded prefix for these versions; unmarked/manual/watch CSS and JavaScript use content hashing. Standard production builds add the CSS marker automatically. Both automatic module tags and manual CSS/JS links use `PublicUrl::versionedAsset()`; pass raw paths, not pre-encoded filenames.

The local `.generated.json` list lets successful builds remove only recorded obsolete output. Keep it between local builds, ignore it in Git, and omit it from deployments. Old unrecorded output requires a reviewed manual cleanup; do not delete handwritten assets.

See [NodeJS Integration](/documentation/nodejs) for tooling and [Upgrade Guide](/documentation/upgrades) for migration.
