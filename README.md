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
| Docs comments | Under every docs page | Held for moderation; staff replies are shown publicly and can be emailed |
| Donate | `/donate` | "Buy me a laptop / PC" goal with progress, bank and mobile-banking details |
| Admin panel | `/dashboard`, `/admin/*` | Inertia + Vue, sidebar, dark mode: tickets, comments, reviews, mail, docs sync, donations, users, roles, SMTP and AI settings |

SEO: per-page titles, descriptions, canonical URLs, Open Graph/Twitter tags, JSON-LD (Organization, WebSite search,
SoftwareApplication with aggregate rating, TechArticle, BreadcrumbList, FAQPage), `sitemap.xml`, and `robots.txt`.
Ticket pages and the admin area are `noindex`.

## Setup

```bash
composer setup                                 # install, .env, key, migrate, build assets
php artisan helpdesk:admin you@example.com     # create an admin, prints a password
composer dev                                   # local development
```

Set `APP_URL` and `SUPPORT_EMAIL` in `.env`. Email can be configured with the `MAIL_*` settings or, from the admin
panel, under **Settings → Email (SMTP)** (with a test button). The AI reply assistant needs an Anthropic API key, set
under **Settings → AI reply assistant** or as `ANTHROPIC_API_KEY`.

### Roles and permissions

Access is managed in the panel (**Users** and **Roles & permissions**), without any package. Each user has at most one
role; users without a role cannot open the panel. Three roles are created: Administrator (everything), Support agent,
and Editor. The permission list lives in `app/Support/Permissions.php`, and each permission is a Gate ability
(`can:tickets.manage` on routes, `$page.props.auth.can['tickets.manage']` in Vue).

## Updating the documentation

Docs come from the dPanel repository. Press **Sync now** on the admin **Documentation** page, or run:

```bash
php artisan docs:sync                    # download from GitHub (branch: DOCS_BRANCH, default main)
php artisan docs:sync /path/to/dpanel    # or copy from a local checkout
```

Every sync is recorded, so the panel shows when the docs last changed and which pages were updated. The web server
user needs write access to `resources/docs`.

Or edit `resources/docs/<page>.md` directly. To add a page, create the Markdown file and add it to `docs` in
`config/site.php`. Links written for GitHub (`installation.md#requirements`, `../SECURITY.md`) are rewritten to site
URLs automatically. Rendered pages are cached until the file changes.

## Tests

```bash
php artisan test
```
