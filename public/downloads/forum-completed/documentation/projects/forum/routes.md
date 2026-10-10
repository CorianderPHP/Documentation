# Forum Routes

In 0.3.0, the folder/filename defines each URL and method. The forum's web handlers belong under `src/Routes/forum-demo`; its JSON handlers belong under `src/Routes/api/forum-demo`.

Remove the temporary `src/Routes/forum-demo.get.php` from setup before creating `forum-demo/index.get.php`.

## Route Map

Paths below are relative to `src/Routes/forum-demo/`.

| File | URL and method | ForumActions method |
| --- | --- | --- |
| `index.get.php` | GET /forum-demo | index |
| `topics/index.get.php` | GET /forum-demo/topics | topics |
| `topics/index.post.php` | POST /forum-demo/topics | storeTopic |
| `topics/[id]/index.get.php` | GET /forum-demo/topics/1 | showTopic |
| `topics/[id]/replies.post.php` | POST /forum-demo/topics/1/replies | storeReply |
| `topics/[id]/replies.get.php` | GET reply URL | Redirect to topic |
| `login.get.php` | GET /forum-demo/login | login |
| `login.post.php` | POST /forum-demo/login | authenticate |
| `logout.post.php` | POST /forum-demo/logout | logout |
| `admin/index.get.php` | GET /forum-demo/admin | admin |
| `admin/users.get.php` | GET /forum-demo/admin/users | adminUsers |
| `admin/users.post.php` | POST /forum-demo/admin/users | updateUserRole |
| `admin/topics.post.php` | POST /forum-demo/admin/topics | moderateTopic |
| `admin/replies.post.php` | POST /forum-demo/admin/replies | moderateReply |

Guests can read. Members can submit topics/replies. Admins can manage roles and moderation. The write service checks each ability; filenames do not provide authorization.

## Connect Read Handlers

Create `src/Routes/forum-demo/index.get.php`:

```php
<?php
use App\Actions\ForumActions;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    (new ForumActions())->index($request);
```

Create `topics/index.get.php` with the same imports and:

```php
return static fn (ServerRequestInterface $request) =>
    (new ForumActions())->topics();
```

Create `topics/[id]/index.get.php` with the same imports and:

```php
return static fn (ServerRequestInterface $request) =>
    (new ForumActions())->showTopic((string) $request->getAttribute('id'));
```

These become usable after the [handler chapter](/guided-projects/forum/handlers) defines the class. Always return the response from the action.

You are here in the flow:

```workflow
Request|GET /forum-demo/topics/1 selects topics/[id]/index.get.php.
Middleware|Validates the matched id before the handler.
Action|ForumActions::showTopic() asks the repository for the topic and replies.
Response|Renders src/Views/forum-demo/topic.php with prepared data.
```

## Validate The Dynamic Id

`[id]` matches any segment. The old regex route constraint is not available. Add `src/Routes/forum-demo/topics/[id]/_middleware.php`:

```php
<?php
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

return [new class implements MiddlewareInterface {
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $id = (string) $request->getAttribute('id');
        return ctype_digit($id) && (int) $id > 0
            ? $handler->handle($request)
            : Responses::html('Topic not found.', 404);
    }
}];
```

All detail/reply handlers in that directory inherit it. Reusable middleware may instead live in `App\Middleware`.

## Connect Form Writes

Create `topics/index.post.php` with the ForumActions/request imports:

```php
return static fn (ServerRequestInterface $request) =>
    (new ForumActions())->storeTopic($request);
```

Create `topics/[id]/replies.post.php`:

```php
return static fn (ServerRequestInterface $request) =>
    (new ForumActions())->storeReply($request, (string) $request->getAttribute('id'));
```

Each action validates through a service, stores a flash, and redirects to a GET page. It must not leave the browser on a submission URL.

Create `topics/[id]/replies.get.php`:

```php
<?php
use CorianderCore\Core\Http\Responses;
use Psr\Http\Message\ServerRequestInterface;

return static fn (ServerRequestInterface $request) =>
    Responses::redirect('/forum-demo/topics/' . $request->getAttribute('id'), 302);
```

This fallback handles bookmarks/back navigation to the reply URL without resubmitting a form.

## Authentication And Admin Files

For `login.get.php`, `login.post.php`, and `logout.post.php`, use the same action/request imports and return these calls respectively:

```php
(new ForumActions())->login();
(new ForumActions())->authenticate($request);
(new ForumActions())->logout();
```

Each call is the **returned expression inside its own callable**, not three statements in one file.

Apply the same pattern to admin files using the methods in the route-map table. Add `admin/_middleware.php` returning `[new ForumDemoAdminMiddleware()]` once the [admin chapter](/guided-projects/forum/admin-area) defines it. It protects every method below that directory, including write routes, while inheriting root CSRF.

The [API chapter](/guided-projects/forum/api) adds the three separate JSON method files.

## Checkpoint

Run `php coriander routes:list`. Check paths/methods and inherited middleware before testing action behavior. GET supplies HEAD fallback. Unknown paths return 404; unsupported methods return 405/Allow.

After implementing handlers/auth, verify guest reads, member writes, admin denial, and redirect-after-write. A 404 is not fixed by including a route file in public/routes.php; that mechanism was removed.

Continue with [Request Handlers](/guided-projects/forum/handlers).
