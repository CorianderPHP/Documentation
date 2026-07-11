# Testing An App

Tests protect your application code while `CorianderCore` can update independently.

## What To Test First

Start with the behavior that would hurt if it broke:

- route smoke tests
- repository methods
- permission services
- validation rules
- Markdown/documentation links, for documentation sites
- generated downloads, for guided projects

## Module Unit Test

Modules are the easiest app code to test because they should not depend on HTTP rendering.

```php
use Modules\Blog\BlogValidator;
use PHPUnit\Framework\TestCase;

final class BlogValidatorTest extends TestCase
{
    public function testTitleIsRequired(): void
    {
        $errors = (new BlogValidator())->validatePost([
            'title' => '',
            'body' => 'Content',
        ]);

        self::assertArrayHasKey('title', $errors);
    }
}
```

## Route Smoke Test

Smoke tests check that important public routes still respond after framework updates.

```php
public function testDocumentationRouteResponds(): void
{
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/documentation';

    ob_start();
    require PROJECT_ROOT . '/public/index.php';
    $output = ob_get_clean();

    self::assertIsString($output);
    self::assertStringContainsString('Documentation', $output);
}
```

Prefer focused route smoke tests over browser-heavy tests for basic coverage.

## Repository Tests

For repository code, use a test database when possible.

SQLite is practical for fast local tests. If production uses MySQL-specific SQL, add at least one integration check against MySQL before release.

## Permission Tests

Permission services should be deterministic.

```php
public function testAdminCanEditAnyPost(): void
{
    $service = new BlogPermissionService();

    self::assertTrue($service->canEdit(
        ['id' => 1, 'role' => 'admin'],
        ['author_id' => 99]
    ));
}
```

Test permission rules once in the permission class, then keep controller tests lighter.

## Documentation App Checks

For documentation websites, add quality checks:

- internal links exist
- code fence languages are supported
- downloads exist
- generated assets exist
- no app code lives under `CorianderCore`

This documentation site uses that pattern in `tests/Docs`.

## Before Merging

Run:

```bash
composer test
php coriander nodejs run build-prod
```

When downloads or assets changed, also run:

```bash
composer generate-downloads
php coriander nodejs run build-prod
```
