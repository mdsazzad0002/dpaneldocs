# Changelog

All notable changes to the dPanel website are documented here.

## [0.3.0] - 2026-10-10

### Added
- Admin panel sections: docs comments, mail (compose + sent log), documentation sync with history, donations, users, roles & permissions, and settings; grouped, collapsible sidebar.
- Comments on documentation pages, moderated, with public staff replies that can also be emailed.
- Public responses to reviews.
- `/donate` page: "Buy me a laptop / PC" goal with progress, bank and mobile-banking details, and donation reports that staff verify (with a thank-you email).
- SMTP settings editable in the panel, with a test email.
- AI reply drafts (Claude) for tickets, comments, reviews and emails.
- `php artisan docs:sync` without a path downloads the docs from GitHub.

### Changed
- Roles and permissions are built in; `spatie/laravel-permission` was removed. Existing admins keep the Administrator role.

## [0.2.0] - 2026-09-29

### Changed
- Documentation is now static Markdown in `resources/docs`, imported from the dPanel repository (`php artisan docs:sync`).
- Redesigned public site: home page, docs with sidebar, table of contents, search, and prev/next navigation.
- Full SEO metadata, JSON-LD structured data, sitemap, and robots rules.

### Added
- Help center with support tickets, private customer ticket links, and email notifications.
- Public reviews with moderation and aggregate star ratings.
- "Was this page helpful?" feedback on every docs page.
- Admin help desk for tickets, reviews, and page feedback; `php artisan helpdesk:admin`.
- Privacy Policy, Terms of Use, and a custom 404 page.

### Removed
- Documentation CMS: posts, categories, AI generation, and version/ZIP management, including the `/api/v1/versions` API.
- Public registration and user/role management screens.

## [0.1.0] - 2026-08-26

### Added
- Fresh Laravel 12 project scaffold.
- Laravel Breeze starter kit with Inertia.js + Vue 3 + Tailwind CSS v4 stack.
- API documentation (`docs/API.md`) covering auth and application routes.
- SQLite database configured for local development.

### Fixed
- Registered the missing `@vitejs/plugin-vue` plugin in `vite.config.js` so `.vue` single-file components compile correctly.
- Restored the Inertia bootstrap code in `resources/js/app.js`.
- Aligned `tailwindcss` npm dependency to `^4.0.0` to match the `@tailwindcss/vite` v4 plugin and v4 CSS syntax.
- Updated `routes/web.php` to render the Inertia `Welcome` page instead of a non-existent Blade `welcome` view.
