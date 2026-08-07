# Recommended App Architecture

CorianderPHP works best when the framework core stays boring and the application owns its feature code. Keep `CorianderCore` replaceable, then organize the app by responsibility.

## The Rule

Do not put project behavior inside `CorianderCore`.

Use app-owned folders:

```structure
src/
  Controllers/
  ApiControllers/
  Middleware/
  Modules/
  Routes/
public/
  public_views/
documentation/
database/
nodejs/
resources/
tests/
```

Framework updates can replace `CorianderCore`. Your app should keep working because controllers, modules, views, routes, migrations, assets, and tests live outside it.

## Responsibility Map

```responsibilities
Controllers|Own the request flow.|Read request data; call app services or repositories; choose the response or view; redirect after successful writes.
Modules|Own reusable app logic.|Repositories; services; validators; permission classes; small feature-specific helpers.
Middleware|Own request gates.|Authentication checks; admin-only areas; API guards; request preconditions.
Views|Own rendering.|HTML structure; escaped output; forms; small display conditions.
```

Views should not own database queries or permission decisions. Prepare the data before rendering.

## Controller Shape

Keep controllers thin:

```php
namespace Controllers;

use CorianderCore\Core\Router\ViewRenderer;
use Modules\Blog\BlogRepository;

final class BlogController
{
    private ViewRenderer $view;

    public function __construct()
    {
        $this->view = new ViewRenderer();
    }

    public function show(string $id): void
    {
        $post = (new BlogRepository())->findPublished((int) $id);

        $this->view->render('blog/show', [
            'post' => $post,
        ]);
    }
}
```

Move query details into the repository. This is an app-owned class under `src/Modules/Blog`, not a framework class:

```php
namespace Modules\Blog;

use CorianderCore\Core\Database\SQLManager;

final class BlogRepository
{
    public function findPublished(int $id): ?array
    {
        $row = SQLManager::sqlScript(
            'SELECT id, title, body FROM posts WHERE id = :id AND status = :status LIMIT 1',
            ['id' => $id, 'status' => 'published']
        );

        return $row === [] ? null : $row;
    }
}
```

## Feature Folder Example

For a blog feature:

```structure
src/
  Controllers/
    BlogController.php
  Modules/
    Blog/
      BlogRepository.php
      BlogService.php
      BlogValidator.php
  Routes/
    blog.php
public/
  public_views/
    blog/
      index.php
      show.php
database/
  migrations/
    20260712000000_create_posts_table.php
```

This keeps the public URL contract, request code, business logic, templates, and schema changes easy to find.

## When To Add A Module

Add a custom module when code is reused, tested independently, or too detailed for a controller.

Good module candidates:

- persistence logic
- permission decisions
- validation rules
- external API clients
- import/export services
- domain-specific write workflows

Do not add a module only to wrap one line. Start simple, then extract when the controller starts hiding the actual request flow.

## Where Validation Belongs

Simple request validation can live near the controller. Reusable validation belongs in a module.

```php
namespace Modules\Blog;

final class BlogValidator
{
    public function validatePost(array $data): array
    {
        $errors = [];

        if (trim((string) ($data['title'] ?? '')) === '') {
            $errors['title'] = 'Title is required.';
        }

        return $errors;
    }
}
```

## Where Permissions Belong

Do not spread permission rules across views, controllers, and middleware.

Create one permission service:

```php
namespace Modules\Blog;

final class BlogPermissionService
{
    public function canEdit(array $user, array $post): bool
    {
        return $user['role'] === 'admin' || $user['id'] === $post['author_id'];
    }
}
```

Then call it from controllers, middleware, and views. The forum guided project uses this pattern for members, moderators, and admins.

## Recommended Growth Path

Start with:

```workflow
Route file|Define the URL contract first.
Controller|Add one request handler for the feature.
View|Render the prepared data in one template.
```

Then add:

```choices
Module|Extract reusable logic when the controller starts hiding the request flow.
Repository|Move data access out when SQL or persistence details grow.
Middleware|Add route gates when access rules repeat.
Tests|Cover behavior that must keep working after framework updates.
```

This keeps small features small while still giving larger features a clear place to grow.
