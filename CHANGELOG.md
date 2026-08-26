# Changelog

All notable changes to Dpanel are documented here.

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
