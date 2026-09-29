# dPanel website

The public website for [dPanel](https://github.com/mdsazzad0002/dpanel), the free, self-hosted web hosting control panel:
product pages, documentation, and a help desk.

There is nothing to manage day to day. The documentation is **static Markdown** that ships with the code, and the only
data the site stores is what visitors send in: support tickets, reviews, and "was this page helpful?" feedback.

## What's inside

| Area | Where | Notes |
| --- | --- | --- |
| Home, legal pages | `resources/views/public` | Blade, server-rendered for SEO |
| Documentation | `resources/docs/*.md` | Listed and ordered in `config/site.php` |
| Help center + tickets | `/support` | Customers follow tickets through a private emailed link, no account |
| Reviews | `/reviews` | Held for moderation, then published with star-rating structured data |
| Help desk (admin) | `/dashboard`, `/admin/*` | Inertia + Vue; answer tickets, moderate reviews, read page feedback |

SEO: per-page titles, descriptions, canonical URLs, Open Graph/Twitter tags, JSON-LD (Organization, WebSite search,
SoftwareApplication with aggregate rating, TechArticle, BreadcrumbList, FAQPage), `sitemap.xml`, and `robots.txt`.
Ticket pages and the admin area are `noindex`.

## Setup

```bash
composer setup                                 # install, .env, key, migrate, build assets
php artisan helpdesk:admin you@example.com     # create an admin, prints a password
composer dev                                   # local development
```

Set `APP_URL`, the `MAIL_*` settings, and `SUPPORT_EMAIL` in `.env` so ticket emails are delivered.

## Updating the documentation

Docs come from the dPanel repository. Pull the latest into this site with:

```bash
php artisan docs:sync /path/to/dpanel
```

Or edit `resources/docs/<page>.md` directly. To add a page, create the Markdown file and add it to `docs` in
`config/site.php`. Links written for GitHub (`installation.md#requirements`, `../SECURITY.md`) are rewritten to site
URLs automatically. Rendered pages are cached until the file changes.

## Tests

```bash
php artisan test
```
