# View Overview

CorianderPHP views live in `public/public_views`. A view folder contains an `index.php` template and a `metadata.php` file.

Use this page to choose the right guide.

## Which View Do I Need?

```choices
Static view|Fixed pages such as home, about, contact, legal pages, or content that does not need request data.|/documentation/static-views
Dynamic view|Controller-prepared pages such as article detail pages, dashboards, forms, permissions, or database content.|/documentation/dynamic-views
Assets|Images, public files, `PUBLIC_URL_PREFIX` handling, or `ImageHandler`.|/documentation/assets
```

## Static Views

Read [Static View Guide](/documentation/static-views) when the page can be rendered directly from a view folder.

Static views are enough when:

- the content is mostly fixed
- the page does not need route parameters
- the page does not submit a form
- the page does not need user-specific permissions
- the page does not read database rows

## Dynamic Views

Read [Dynamic View Guide](/documentation/dynamic-views) when a controller needs to load data and pass it to a template.

Dynamic views are needed when:

- the URL contains a route parameter such as `/articles/{id}`
- the page displays database rows
- the page depends on the current user or permissions
- the page renders validation errors or old form input
- the page needs flash messages after a redirect

## Shared Rules

- Keep templates focused on presentation.
- Do not run database queries in views.
- Do not make permission decisions only in views.
- Store assets under `public/assets`.
- Use [Assets And Images](/documentation/assets) when rendering images with framework asset-path handling.
