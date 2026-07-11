# Start a CorianderPHP Project

This page is the shortest practical path from a fresh CorianderPHP install to a real feature. It does not prescribe what the feature should be. Pick a small first feature from your own project, then use the same structure for routes, controller, view, module, assets, and optional database.

## What You Are Building

Choose a feature slug, such as `account`, `catalog`, `blog`, or `support`. In the examples below, replace `feature` with your slug and replace `Feature` with the matching PascalCase name.

Your first feature should use this structure:

```structure
src/
  Routes/feature.php
  Controllers/FeatureController.php
  Modules/Feature/FeatureSummary.php

public/public_views/feature/index.php
nodejs/src/feature/index.ts
```

The goal is to understand where project code belongs.

## Update-Safe Rule

Do not put app features inside `CorianderCore`. Framework updates manage that directory. If you need app-specific routes, controllers, middleware, modules, views, or assets, place them in the project folders shown below.

## 1. Install Dependencies

From the project root:

```bash
composer install
php coriander nodejs install
```

Composer installs PHP dependencies. The Node command installs the Tailwind and TypeScript tooling used by the `nodejs` folder.

## 2. Configure `.env`

Start with a local environment:

```env
APP_ENV=local
APP_DEBUG=1
APP_TIMEZONE=Europe/Paris
PROJECT_URL=http://localhost
PUBLIC_URL_PREFIX=/public
```

Use `PUBLIC_URL_PREFIX=/public` when Apache serves the project root. Leave it empty only when the web server document root is already the `public/` directory.

Checkpoint: open your local project URL. CSS should load from `/public/assets/css/output.css`.

## 3. Create A Feature Route File

Create a route file for your feature:

```bash
php coriander make:route feature
```

The file should live here:

```structure
src/Routes/feature.php
```

Add a route:

```php
<?php
declare(strict_types=1);

use Controllers\FeatureController;
use CorianderCore\Core\Router\Router;

return static function (Router $router): void {
    $router->get('feature', static fn () => (new FeatureController())->index());
};
```

Why this matters: `public/routes.php` should stay small. Feature URLs belong in feature route files.

## 4. Register The Route File

In `public/routes.php`, include the feature route file:

```php
$featureRoutes = PROJECT_ROOT . '/src/Routes/feature.php';
if (is_file($featureRoutes)) {
    (require $featureRoutes)($router);
}
```

Checkpoint: `/feature` should now be handled by the framework router. It may fail until the controller exists, but it should no longer be an Apache directory or missing static file issue.

## 5. Create The Controller

Create the controller:

```bash
php coriander make:controller Feature
```

Then keep it thin:

```php
<?php
declare(strict_types=1);

namespace Controllers;

use CorianderCore\Core\Router\ViewRenderer;
use Modules\Feature\FeatureSummary;

final class FeatureController
{
    public function index(): void
    {
        (new ViewRenderer())->render('feature', [
            'summary' => (new FeatureSummary())->items(),
        ]);
    }
}
```

The controller coordinates the request. It does not build HTML and it does not own reusable business logic.

## 6. Create A Custom App Module

Create the module folder:

```structure
src/Modules/Feature/
```

Create `src/Modules/Feature/FeatureSummary.php`:

```php
<?php
declare(strict_types=1);

namespace Modules\Feature;

final class FeatureSummary
{
    public function items(): array
    {
        return [
            ['label' => 'Routes', 'value' => 'Configured'],
            ['label' => 'Controller', 'value' => 'Thin'],
            ['label' => 'View', 'value' => 'Rendered'],
        ];
    }
}
```

This is a custom app module. It is different from official Coriander modules inside the framework repository. Custom modules are for project-specific behavior and should stay in `src/Modules`.

## 7. Create The View

Create the view:

```bash
php coriander make:view feature
```

Expected file:

```structure
public/public_views/feature/index.php
```

Render the data passed by the controller:

```html
<?php
/** @var array<int,array{label:string,value:string}> $summary */
$summary = $summary ?? [];
?>

<section class="px-4 py-10 font-poppins sm:px-6 lg:px-8">
    <h1 class="font-concert-one text-5xl text-dark-green dark:text-mint">Feature</h1>

    <div class="mt-8 divide-y divide-dark-green/10 dark:divide-mint/10">
        <?php foreach ($summary as $item): ?>
            <div class="grid gap-2 py-4 md:grid-cols-[12rem_1fr]">
                <span class="font-semibold"><?= htmlspecialchars($item['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                <span><?= htmlspecialchars($item['value'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>
```

Checkpoint: open `/feature`. You should see a server-rendered page with the summary rows.

## 8. Add TypeScript Only For Interaction

If the page needs small interactions, create:

```structure
nodejs/src/feature/index.ts
```

Example:

```ts
document.querySelectorAll('[data-feature-action]').forEach((button) => {
  button.addEventListener('click', () => {
    button.classList.add('opacity-80');
  });
});
```

Build assets:

```bash
php coriander nodejs run build-all
```

The compiled file goes to:

```structure
public/assets/js/feature/index.js
```

The footer automatically loads a matching view script when it exists.

## 9. Add Middleware When An Area Needs Protection

Use middleware for an entire protected area. Do not repeat the same access rule inside every controller method.

```php
$router->group('admin', [new AdminMiddleware()], static function (Router $router): void {
    $router->get('feature', static fn () => (new FeatureController())->index());
});
```

Project middleware belongs in:

```structure
src/Middleware/
```

## 10. Add Database Only When You Need Persistence

Do not start every feature with a database. Add it when the feature needs persisted data.

```bash
php coriander make:database
php coriander make:migration CreateFeatureTables
php coriander migrate
```

For simple reads and writes, prefer the safer condition helpers:

```php
SQLManager::findWhere(['id', 'title'], 'topics', ['category_id' => $categoryId]);
SQLManager::updateWhere('topics', ['title' => $title], ['id' => $topicId]);
SQLManager::deleteWhere('topics', ['id' => $topicId]);
```

## Definition Of Done

Your first feature is correctly shaped when:

- Your feature URL is registered in a feature route file.
- The controller renders a view and delegates reusable logic.
- Custom project logic lives in `src/Modules`.
- Templates live in `public/public_views`.
- TypeScript lives in `nodejs/src` only when the page needs interaction.
- No app feature code was added to `CorianderCore`.

## Next

Read the focused references when you need details:

- [Routing](/docs/routing)
- [Controllers](/docs/controllers)
- [Views](/docs/views)
- [Custom Modules](/docs/modules)

Then follow the complete [Forum Guided Project](/guided-projects/forum) to see these ideas connected in a real feature.
