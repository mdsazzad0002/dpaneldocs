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

## Frontend Pages

Inertia pages live in `resources/js/Pages`. Layouts live in `resources/js/Layouts`. Reusable components live in `resources/js/Components`.
