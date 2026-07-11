# Forum Routes

Routes are the public contract of the feature. The forum has enough URLs that it should use `src/Routes/forum-demo.php` instead of putting everything directly in `public/routes.php`.

## Goal

Create public read routes, session routes, member write routes, and admin moderation routes.

## File Edited

```structure
src/Routes/forum-demo.php
```

## Route Map

```txt
GET  /forum-demo
GET  /forum-demo/topics
POST /forum-demo/topics
GET  /forum-demo/topics/{id}
GET  /forum-demo/topics/{id}/replies
POST /forum-demo/topics/{id}/replies

GET  /forum-demo/login
POST /forum-demo/login
POST /forum-demo/logout

GET  /forum-demo/admin
GET  /forum-demo/admin/users
POST /forum-demo/admin/users
POST /forum-demo/admin/topics
POST /forum-demo/admin/replies
```

This map shows the permission model before any controller code exists:

- Guests can read the forum and log in.
- Members can create topics and replies.
- Admins can manage users, topics, and replies.

## Step: Import The Classes

Open the generated `src/Routes/forum-demo.php` file from the setup chapter. Replace the starter route with the controller, router, middleware, response, and request imports you need for the real forum routes.

```php
<?php
declare(strict_types=1);

use Controllers\ForumDemoController;
use CorianderCore\Core\Router\Router;
use Middleware\ForumDemoAdminMiddleware;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
```

The route file returns a closure so `public/routes.php` can include it cleanly.

## Step: Add Public Read Routes

Public read routes do not need login. Guests can open the forum landing page, topic list, and topic detail.

```php
return static function (Router $router): void {
    $router->get('forum-demo', static fn () => (new ForumDemoController())->index());

    $router->get('forum-demo/topics', static fn () => (new ForumDemoController())->topics());

    $router->get('forum-demo/topics/{id:[0-9]+}', static fn (ServerRequestInterface $request) =>
        (new ForumDemoController())->showTopic((string) $request->getAttribute('id'))
    );
};
```

The `{id:[0-9]+}` constraint matters. It keeps `/forum-demo/topics/create` or other future named routes from being mistaken for a numeric topic detail route.

## Step: Understand The Request Flow

Before adding write routes, separate URLs into two groups:

- Read URLs return pages and are safe to refresh.
- Write URLs receive form submissions and should redirect after work is done.

For this project, a reply submission follows this path:

```txt
POST /forum-demo/topics/1/replies
  -> ForumDemoController::storeReply()
  -> PublicDemoWriteGuard or ForumWriteService
  -> store a flash result
  -> 302 redirect to /forum-demo/topics/1
GET /forum-demo/topics/1
  -> render topic and consume flash
```

This pattern is called Post/Redirect/Get. It prevents browser warnings like "confirm form resubmission" when a user refreshes or presses Back.

## Step: Add Authentication Routes

The demo uses fixed accounts and session state.

```php
$router->get('forum-demo/login', static fn () => (new ForumDemoController())->login());

$router->post('forum-demo/login', static fn (ServerRequestInterface $request) =>
    (new ForumDemoController())->authenticate($request)
);

$router->post('forum-demo/logout', static fn () => (new ForumDemoController())->logout());
```

Use `POST` for login and logout because both change session state. These web forms should include framework CSRF tokens.

## Step: Add Member Write Routes

These web routes are protected by the framework CSRF middleware. Permission checks still happen in the write service.

```php
$router->post('forum-demo/topics', static fn (ServerRequestInterface $request) =>
    (new ForumDemoController())->storeTopic($request)
);

$router->post('forum-demo/topics/{id:[0-9]+}/replies', static fn (ServerRequestInterface $request) =>
    (new ForumDemoController())->storeReply($request, (string) $request->getAttribute('id'))
);
```

The topic write route creates an original post. The reply route creates secondary discussion under an existing topic.

Add a small GET redirect for the reply URL as a fallback:

```php
$router->get('forum-demo/topics/{id:[0-9]+}/replies', static fn (ServerRequestInterface $request) =>
    new Response(302, ['Location' => '/forum-demo/topics/' . (string) $request->getAttribute('id')], '')
);
```

Users should never stay on a form-submit URL. If the browser revisits `/forum-demo/topics/1/replies`, send it back to `/forum-demo/topics/1`.

Do not put validation in the route closure. The route only selects the controller method and passes route attributes. The controller can load the topic, and the write service can validate the submitted content.

## Step: Add Admin Route Group

Group admin routes so authorization middleware is declared once.

```php
$router->group('forum-demo/admin', [new ForumDemoAdminMiddleware()], static function (Router $admin): void {
    $admin->get('', static fn () => (new ForumDemoController())->admin());
    $admin->get('users', static fn () => (new ForumDemoController())->adminUsers());
});
```

Every route in this group requires `admin.view` through `ForumDemoAdminMiddleware`.

## Step: Add Admin Moderation Writes

Admin writes are separate from member writes because they change moderation state instead of discussion content.

```php
$router->group('forum-demo/admin', [new ForumDemoAdminMiddleware()], static function (Router $admin): void {
    $admin->get('', static fn () => (new ForumDemoController())->admin());
    $admin->get('users', static fn () => (new ForumDemoController())->adminUsers());

    $admin->post('users', static fn (ServerRequestInterface $request) =>
        (new ForumDemoController())->updateUserRole($request)
    );

    $admin->post('topics', static fn (ServerRequestInterface $request) =>
        (new ForumDemoController())->moderateTopic($request)
    );

    $admin->post('replies', static fn (ServerRequestInterface $request) =>
        (new ForumDemoController())->moderateReply($request)
    );
});
```

These routes map to actions like:

- update a user role
- lock or unlock a topic
- hide a topic
- hide a reply

Treat these POST URLs as action routes, not destination pages. A topic lock can be submitted from the topic detail page or from the moderation queue. The form should send a small context field such as `return_to=topic` or `return_to=admin`, and the controller should redirect to the matching GET page with a flash result.

The public documentation demo validates these actions but does not persist visitor changes. A local SQLite build should persist them through `ForumWriteService`.

The admin route group protects access to the URLs. The write service still checks abilities like `topic.lock`, `reply.moderate`, and `user.manage`. That duplication is intentional: middleware protects whole screens, while the write service protects individual actions.

## Step: Keep Route Responsibilities Small

Routes should not:

- query the database
- inspect roles
- validate form fields
- render templates directly

Routes should:

- choose the controller action
- pass the request when the action needs form data or route attributes
- group middleware around related URLs

## Checkpoint

Open these URLs:

- [/forum-demo](/forum-demo)
- [/forum-demo/topics](/forum-demo/topics)
- [/forum-demo/topics/1](/forum-demo/topics/1)
- [/forum-demo/admin](/forum-demo/admin)
- [/forum-demo/admin/users](/forum-demo/admin/users)

The admin URLs should redirect guests and members to login. The admin account should enter the protected area.

## Common Mistakes

- Registering generic topic routes before more specific named topic routes.
- Forgetting `ServerRequestInterface` when a route needs request attributes or form data.
- Putting permission checks in the route closure instead of middleware or services.
- Putting API endpoints here. The demo API uses the framework API controller convention.

## Next

Continue with [Controllers](/guided-projects/forum/controllers).
