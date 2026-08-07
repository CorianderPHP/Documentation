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

```workflow
Project structure|Create app-owned files so framework updates can replace `CorianderCore` without deleting forum code.
SQLite data model|Add users, categories, topics, replies, moderation events, repositories, and seed data.
Routes|Define URLs before controllers so public pages, member writes, admin actions, and API endpoints have a readable contract.
Controllers|Build thin request coordinators that call modules, use Post/Redirect/Get, and render prepared view data.
Views|Create forum lists, topic pages, forms, login, and admin screens while escaping every public string.
Permissions and writes|Add authentication, permissions, admin middleware, and a write service used by web forms and API endpoints.
Protected demo|Prevent hosted public demo writes from storing unsafe visitor text.
Production path|Finish with API endpoints and notes for moving from SQLite to MySQL.
```

## How To Read Each Chapter

Every chapter should answer four questions before you copy code:

- What file am I creating or editing?
- Which layer owns this responsibility?
- What does this step need from the previous step?
- How do I know it worked?

When a chapter introduces a form write, follow the full request lifecycle: route, controller, permission check, write service, redirect, flash message, and rendered GET page. That is the difference between a demo that appears to work and an app that behaves correctly when users refresh or go back.

When you still feel lost, place the current file in this lifecycle:

```workflow
Route|Maps the URL to a controller action.
Controller|Reads route attributes or form data, then asks modules for work.
Module or repository|Loads data, checks permissions, validates input, or writes through SQL.
Response|Renders a view for GET requests or redirects after POST requests.
View|Displays only the prepared variables it receives.
```

- If you are in a route file, ask which controller action receives this URL.
- If you are in a controller, ask which module gives it data and which view receives it.
- If you are in a module, ask which controller calls it and what result shape it returns.
- If you are in a view, ask which controller rendered it and which variables were passed.

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

- Putting forum logic inside `CorianderCore`. Keep project logic in `src`, `public/public_views`, `documentation`, `database`, and `nodejs`.
- Teaching the public demo protection as the normal persistence layer. Local projects should write to a real database.
- Hiding admin buttons in views but forgetting server-side permission checks.

## Next

Continue with [Project Structure](/guided-projects/forum/setup).
