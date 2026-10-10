# Forum Completed Demo Reference

These are app-owned files for **CorianderPHP 0.3.0**. The framework is not included. This is the documentation site's read-only demo reference, not the local persisted forum described in the SQL chapters.

## Install In A Fresh Local Starter

1. Download/install the 0.3.0 framework and its Composer/Node dependencies.
2. Copy this package's src, nodejs, and public assets into that starter, except src/Routes/_middleware.php. The bundled layout/theme is intended for a fresh app; merge instead of replacing existing app layouts/assets.
3. Keep the starter's public/index.php and root src/Routes/_middleware.php. The package includes a sample root policy for comparison; do not exempt api/forum-demo from CSRF.
4. Use the starter's `"App\\": "src/"` Composer mapping and run composer dump-autoload.
5. Run php coriander nodejs run build-prod, then php coriander routes:list.
6. Visit /forum-demo. Use admin@example.com / demo-admin or user@example.com / demo-user.

Method files under src/Routes are discovered automatically. Do not include an old forum-demo.php registration file. Private templates are under src/Views; the included root layouts link back to the online guide.

## Behavior And Security

DemoForumRepository provides fixed example data. DemoWriteGuard validates permitted actions and returns fake success without storing submitted content. ForumActions explicitly starts sessions for authentication and flashes, then returns PSR view/redirect responses.

The JSON endpoints share the login cookie. Send `csrf_token` in the JSON body with that same session cookie. Root framework middleware validates it before ForumApiActions. A token header alone is not supported.

Never deploy these fixed accounts or quick-role login as real authentication.

## Request Flow

```workflow
Method file|Maps the URL and HTTP method to an action.
Action|Prepares view data or delegates a protected demo write.
Module|Resolves demo users, permissions, data, and write safety.
Response|Renders a private template or redirects after POST.
```

Learner comments identify which action renders each template. See the included documentation/projects/forum chapters or https://corianderphp.com/guided-projects/forum for the real SQLite repositories/write service and MySQL notes.

Do not edit CorianderCore; framework updates replace it.
