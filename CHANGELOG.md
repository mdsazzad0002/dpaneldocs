# Changelog

All notable changes to the dPanel website are documented here.

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
