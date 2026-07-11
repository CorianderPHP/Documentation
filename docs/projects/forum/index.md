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

1. Create the app-owned project structure so framework updates can replace `CorianderCore` without deleting your forum code.
2. Add the SQLite data model for users, categories, topics, replies, moderation events, repositories, and seed data.
3. Define routes before controllers so URLs become a readable contract for public pages, member writes, admin actions, and API endpoints.
4. Build thin controllers that coordinate requests, call modules, use Post/Redirect/Get, and render prepared view data.
5. Create views for forum lists, topic pages, forms, login, and admin screens while escaping every public string.
6. Add authentication, permissions, admin middleware, and the write service so web forms and API endpoints use the same authorization rules.
7. Protect the public demo writes so the official site can validate visitor actions without storing unsafe text.
8. Finish with API endpoints and production notes for moving the same structure from SQLite to MySQL.

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
