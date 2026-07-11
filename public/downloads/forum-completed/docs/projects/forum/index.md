# Build a Forum with Permissions

This guided project builds a small forum with real persistence. You will create routes, controllers, custom modules, middleware, views, API endpoints, and a SQLite database without touching `CorianderCore`.

## Goal

Build a public forum where visitors can read categories, topics, and replies, then log in with demo accounts to test member and admin permissions.

The protected live demo is at [/forum-demo](/forum-demo). Keep it open while reading the guide, but treat the guide as the source of truth for the project you build locally.

## What The Finished Project Does

- Guests can read public forum content.
- Members can create topics and replies in a local SQLite database.
- Admins can open the admin area and perform moderation-style actions.
- Web forms and API endpoints use the same permission rules.
- The public documentation demo blocks persistence for visitor-written content, so the official site stays safe.

## Demo Accounts

```txt
Admin account
Email: admin@example.com
Password: demo-admin

Member account
Email: user@example.com
Password: demo-user
```

## Files You Will Build

```structure
database/
  forum.sqlite
  migrations/

src/
  Routes/forum-demo.php
  Controllers/ForumDemoController.php
  ApiControllers/ForumDemoController.php
  Middleware/ForumDemoAdminMiddleware.php
  Modules/ForumDemo/
    Auth/DemoAuth.php
    Data/ForumRepository.php
    Data/UserRepository.php
    Permissions/DemoPermissionService.php
    Writes/ForumWriteService.php
    Writes/PublicDemoWriteGuard.php

public/public_views/forum-demo/
  index.php
  login/index.php
  topics/index.php
  topic/index.php
  admin/index.php
  admin-users/index.php
```

## Tutorial Path

Use the sidebar order. The point is not memorizing every line. The point is learning where each responsibility belongs.

| Step | What you build | Why it matters |
| --- | --- | --- |
| Project structure | App-owned folders for routes, controllers, modules, views, and migrations. | Framework updates can replace `CorianderCore` without deleting your forum code. |
| SQLite data model | Users, categories, topics, replies, moderation events, repositories, and seed data. | The project starts with real persistence instead of fake arrays. |
| Routes | Public read routes, member writes, login/logout, admin writes, and API URLs. | URLs become a readable contract before controller code grows. |
| Controllers | Thin request coordinators with Post/Redirect/Get and flash messages. | Controllers stay focused on flow instead of owning business rules. |
| Views | Forum lists, topic pages, forms, login, and admin screens. | Templates render prepared data and escape every public string. |
| Authentication | Fixed demo accounts stored in session. | The permission examples need a current user without building a full auth product. |
| Permissions | A single ability matrix for guests, members, moderators, and admins. | Web forms, middleware, write services, and API endpoints ask the same questions. |
| Admin middleware | A protected route group for `/forum-demo/admin`. | Admin pages are protected server-side, not only hidden in the UI. |
| Write service | Validation, authorization, `sqlScript()` writes, and moderation audit events. | Persistence is reusable from web and API controllers. |
| Protected demo writes | Public-site write protection that validates but does not save visitor content. | The official demo can be interactive without storing unsafe text. |
| API endpoints | JSON writes that reuse the same permission and write-service rules. | The project shows both browser and API entry points. |
| MySQL and production | How to move the same app structure from SQLite to MySQL. | Learners see where local demo decisions change for production. |

## How To Read Each Chapter

Every chapter should answer four questions before you copy code:

- What file am I creating or editing?
- Which layer owns this responsibility?
- What does this step need from the previous step?
- How do I know it worked?

When a chapter introduces a form write, follow the full request lifecycle: route, controller, permission check, write service, redirect, flash message, and rendered GET page. That is the difference between a demo that appears to work and an app that behaves correctly when users refresh or go back.

## Architecture Rule

Keep each layer boring:

- Routes map URLs to handlers.
- Controllers call modules and render responses.
- Modules own reusable logic.
- Middleware protects route groups.
- Views display prepared data.

That gives you SOLID boundaries without adding heavy abstractions.

## Checkpoint

Open [/forum-demo](/forum-demo). You should see the protected live demo. When you build the project locally, the same screens should read and write through SQLite.

## Downloads

The guide is the primary path, but the completed app download is useful for comparison. This package does not include the CorianderPHP framework; create or use a CorianderPHP project first, then place these files inside it.

- [Download completed app package](/public/downloads/forum-completed.zip) when you want to compare your implementation against the reference demo.

Do not copy the completed package blindly into an existing app. Read the guide first, then use the completed version to resolve differences.

## Common Mistakes

- Putting forum logic inside `CorianderCore`. Keep project logic in `src`, `public/public_views`, `docs`, `database`, and `nodejs`.
- Teaching the public demo protection as the normal persistence layer. Local projects should write to a real database.
- Hiding admin buttons in views but forgetting server-side permission checks.

## Next

Continue with [Project Structure](/guided-projects/forum/setup).
