# Dpanel API Documentation

## Overview

Dpanel is a Laravel application using the [Inertia.js](https://inertiajs.com/) + [Vue 3](https://vuejs.org/) stack, styled with [Tailwind CSS v4](https://tailwindcss.com/). Authentication scaffolding is provided by [Laravel Breeze](https://laravel.com/docs/starter-kits#breeze-and-inertia).

- **Backend:** Laravel 12, PHP 8.3
- **Frontend:** Vue 3 via Inertia.js
- **Styling:** Tailwind CSS v4
- **Build tool:** Vite
- **Database:** SQLite (default, configurable via `.env`)

## Getting Started

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run build   # or `npm run dev` for local development
php artisan serve
```

## Authentication Routes

Provided by Laravel Breeze (`routes/auth.php`):

| Method | URI | Name | Description |
|---|---|---|---|
| GET | `/login` | `login` | Show login form |
| POST | `/login` | — | Authenticate user |
| GET | `/register` | `register` | Show registration form |
| POST | `/register` | — | Register new user |
| POST | `/logout` | `logout` | Log out current user |
| GET | `/forgot-password` | `password.request` | Show password reset request form |
| POST | `/forgot-password` | `password.email` | Send password reset link |
| GET | `/reset-password/{token}` | `password.reset` | Show password reset form |
| POST | `/reset-password` | `password.store` | Reset password |
| GET | `/verify-email` | `verification.notice` | Show email verification notice |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | Verify email |
| POST | `/email/verification-notification` | `verification.send` | Resend verification email |
| GET | `/confirm-password` | `password.confirm` | Show password confirmation form |
| POST | `/confirm-password` | — | Confirm password |
| PUT | `/password` | `password.update` | Update password |

## Application Routes

Defined in `routes/web.php`:

| Method | URI | Name | Description |
|---|---|---|---|
| GET | `/` | — | Welcome page (Inertia) |
| GET | `/dashboard` | `dashboard` | Authenticated user dashboard |
| GET | `/profile` | `profile.edit` | Edit profile |
| PATCH | `/profile` | `profile.update` | Update profile |
| DELETE | `/profile` | `profile.destroy` | Delete account |

## Adding a New API Endpoint

1. Define the route in `routes/web.php` (Inertia pages) or `routes/api.php` (JSON API).
2. Create/update a controller in `app/Http/Controllers`.
3. For Inertia responses, return `Inertia::render('PageName', [...props])`.
4. For pure JSON API responses, return `response()->json([...])` or an API Resource.
5. Document the new endpoint in this file and note the change in `CHANGELOG.md`.

## Version / Update-Check API

Public, unauthenticated, read-only JSON API for checking the latest published release of a
documentation post that has downloadable versions attached (e.g. the dPanel software itself,
published here as a documentation post with `.zip` versions uploaded through the admin UI).
Any external app — including the dPanel control panel at `/var/www/dpanel` — can poll this to
compare its own version against the latest release and prompt an update, without needing an
API key or any change to this project.

Defined in `routes/api.php`, handled by `App\Http\Controllers\Api\VersionController`.
Rate limited to 60 requests/minute per IP. Only `published` documentation posts are ever
returned — pending/rejected posts 404. Every version object is a real uploaded file; nothing
is ever synthesized. Each version's `install_guide` is whatever the admin wrote for that
specific release (lightweight markup — `#`/`##`/`###` headings, `- ` list items) and is `null`
when none was written; it is never invented. If a product has no versions uploaded yet, the response says so honestly
(`"latest": null`, `"versions": []`) instead of a 404 or fake placeholder entry.

### `GET /api/v1/versions/{slug}`

Returns every real, published version for the given documentation post's slug (the same slug
used in its public URL, `/docs/{slug}`), newest first, so the caller can see all of them and
download whichever one it actually needs — not just the newest.

```bash
curl https://dpanel.dengrweb.com/api/v1/versions/getting-started-with-dpanel
```

```json
{
    "product": "Getting Started with dPanel",
    "slug": "getting-started-with-dpanel",
    "latest": {
        "version": "1.0.0",
        "changelog": "Initial test release.",
        "install_guide": "# Install\n\n1. Download and extract the ZIP\n2. Run ./installer.sh",
        "file_name": "dpanel-1.0.0.zip",
        "file_size": 12345,
        "downloads": 0,
        "released_at": "2026-08-26T15:16:04+00:00",
        "download_url": "https://dpanel.dengrweb.com/docs/getting-started-with-dpanel/download/{versionId}"
    },
    "versions": [
        { "version": "1.0.0", "...": "same shape as latest" }
    ]
}
```

If the post exists but has no versions uploaded yet:

```json
{
    "product": "Getting Started with dPanel",
    "slug": "getting-started-with-dpanel",
    "latest": null,
    "versions": []
}
```

`404` with `{"message": "..."}` only when the slug itself doesn't exist or isn't published —
never for "no versions yet", which is a normal empty state, not an error.

### `GET /api/v1/versions/{slug}/latest`

Lightweight shortcut — same rules, but returns just `{"product", "slug", "latest"}` (no full
`versions` array) for callers that only care about the newest release.

### Integration example (consumer side, e.g. `/var/www/dpanel`)

This project does not call out to any other app — the consumer is responsible for polling.
A typical integration on the consumer side looks like:

```php
$response = Http::get('https://dpanel.dengrweb.com/api/v1/versions/dpanel-releases/latest');
$latest = $response->json('latest.version');

if ($latest && version_compare($latest, config('app.version'), '>')) {
    // show an "update available" notice, using latest.download_url and latest.changelog
}
```

Whichever documentation post is meant to represent a given product's releases just needs its
`.zip` files uploaded as versions through the normal admin UI (`/documentation/{id}/edit`) —
there is no separate "release" concept to configure.

## Frontend Pages

Inertia pages live in `resources/js/Pages`. Layouts live in `resources/js/Layouts`. Reusable components live in `resources/js/Components`.
