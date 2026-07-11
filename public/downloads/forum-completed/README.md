# CorianderPHP Forum Completed App Reference

Use this package as a reference implementation for the guided forum project.

This is not a full CorianderPHP project and it does not include the framework. The files are copied from the documentation demo and are meant to be placed inside an existing CorianderPHP application.

The hosted public demo protects visitor writes, but the guide explains where local SQLite persistence belongs when you build the project yourself.

## Included Areas

- `src/Routes/forum-demo.php`
- `src/Controllers/ForumDemoController.php`
- `src/ApiControllers/ForumDemoController.php`
- `src/Middleware/ForumDemoAdminMiddleware.php`
- `src/Modules/ForumDemo`
- `public/public_views/forum-demo`
- `nodejs/src/forum-demo`
- `documentation/projects/forum`

## Important

Do not put project code in `CorianderCore`. Keep app behavior in app-owned folders so framework updates can replace the core safely.
