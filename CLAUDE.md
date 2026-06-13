# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
php artisan serve --port=6767   # local dev server (http://127.0.0.1:6767)
npm run dev                      # Vite asset watcher (run alongside artisan serve)
npm run build                    # production asset build
php artisan migrate:fresh --seed # reset DB and re-seed
php artisan route:list           # list all routes
php artisan view:clear           # clear compiled Blade views
php artisan tinker --execute="..." # one-off PHP (avoid interactive tinker on Windows — BOM issue)
```

**Dev seed credentials:**
- Admin: `admin@rapidinsightdesigns.com` / `password`
- Staff: `staff@rapidinsightdesigns.com` / `password`
- Customer: `customer@example.com` / `password`

There are no automated tests.

## Deployment (production — GoDaddy)

Production is a GoDaddy shared host. `public_html` is the doc root (not Laravel's `public/`); `public_html/build` and `public_html/storage` are **symlinks** into the repo's `public/build` and `storage/app/public`. Production uses **MySQL** (dev is SQLite). Deploys are git-based: push to `origin/main`, then on the server `git pull` + clear/rebuild caches.

**⚠️ Critical: rebuild assets whenever you add a new CSS class.** `public/build/` (compiled Vite/Tailwind output) **is committed to git** and shipped via `git pull`. Locally `npm run dev` generates Tailwind classes on the fly so dev *always* looks right — but production serves the pre-compiled bundle. If a Blade edit introduces **any** new Tailwind class (a new utility, an arbitrary value like `h-[520px]`, or a new responsive variant like `xl:flex-row`) and you don't run `npm run build` + commit `public/build`, those classes **silently have no effect on production**. (Real incident: the `h-[520px]` class wasn't in the prod CSS, so an absolutely-positioned `<img>` collapsed to zero height and showcase images vanished — dev looked fine the whole time.)

A deploy is "Blade-only, no build" **only** if it reused already-existing classes. If any class name changed: `npm run build` → `git add public/build` → commit → push. The CSS filename is content-hashed, so browser caches bust automatically (no manual cache-busting needed).

**Server post-pull steps:**
```bash
git pull origin main
php artisan migrate --force                    # only if new migrations
php artisan view:clear && php artisan view:cache  # always, after Blade/asset changes
php artisan config:cache && php artisan route:cache # only if config or routes changed
```

The `laravel-deploy-packager` agent runs the push side; tell it explicitly when assets were rebuilt (it otherwise tends to assume "no build needed").

## Architecture

### Route Groups

| Prefix | Middleware | Description |
|---|---|---|
| `/` | none | 5 public pages |
| `/login`, `/forgot-password`, `/reset-password` | `guest` | Auth routes |
| `/showroom/*` | `auth` | Customer demo portal (all roles) |
| `/staff/*` | `auth`, `role:staff,admin` | Staff portal |
| `/admin/*` | `auth`, `role:admin` | Admin portal |

### Auth

Single `User` model with `role` column: `admin`, `staff`, `customer`.

- `EnsureRole` middleware (`app/Http/Middleware/`) — registered as `role:` alias in `bootstrap/app.php`
- Login flow: Alpine.js modal on all public pages → `POST /login` → `LoginController`
  - Redirects: admin → `/admin/dashboard`, staff → `/staff/dashboard`, customer → `/showroom`
- `loginModal()` Alpine component in `resources/js/app.js` — persists email via `$persist` (localStorage key `rid_email`)

### Models

| Model | Table | Notes |
|---|---|---|
| `User` | `users` | role enum: `admin/staff/customer`; helpers: `isAdmin()`, `isStaff()`, `isCustomer()`, `isStaffOrAdmin()` |
| `ShowroomItem` | `showroom_items` | `tech_tags` comma-separated string (`techTagsArray()`); `scopeActive()`; `slides()` hasMany. Public-preview columns: `preview_url` + `preview_html_path` (file on `public` disk) + `preview_mode` (`frame`\|`window`). Helpers: `previewUrl()` (URL wins over uploaded file), `previewMode()`, `hasPreview()` |
| `ShowcaseSlide` | `showcase_slides` | Belongs to `ShowroomItem`; `bullets` cast to array (JSON); `image_path` on `public` disk; ordered by `sort_order` |
| `CustomerShowroomAccess` | `customer_showroom_access` | Pivot model; `granted_by` FK to users |
| `Inquiry` | `inquiries` | status: `new/in_progress/resolved`; `statusBadgeClass()` returns CSS class |
| `SiteContent` | `site_contents` | Key-value CMS; `SiteContent::get($key, $default)` / `SiteContent::set($key, $value)` |

### ShowRoom Access

- Staff/admin see all active ShowroomItems
- Customers see only items in `customer_showroom_access` pivot
- Grant/revoke via admin `/admin/showcase` → Customer Access tab
- `ShowroomController::show()` enforces access check for customers

### Public Showcase Preview (`resources/views/public/showcase.blade.php`)

Clicking a card opens a preview. The whole panel is a single Alpine `x-data` component; behavior is driven per-item by `previewJson` (built from the model's `previewUrl()`/`previewMode()`):

- **Has preview + `frame` mode** → fixed, centered overlay at **90vw** with a sandboxed `<iframe>` (skeleton loader, "Open in new tab" + Close, click-outside/Esc to close). Note: external sites that send `X-Frame-Options`/CSP `frame-ancestors` refuse to embed — those need `window` mode.
- **Has preview + `window` mode** → opens the URL in a new tab on click (card button reads "↗ Live Preview").
- **No preview** → falls back to the image **slideshow** panel (built from `$item->slides`): auto-advancing tabs, lightbox with left/right arrow + keyboard cycling, dots/progress footer. The slideshow panel also breaks out to **90vw** (via `margin-left: calc(50% - 45vw)`; `overflow-x: clip` lives on the inner cards grid so the wide panel isn't clipped). On `xl`+ the slide image (`h-[520px]`, fixed) and text sit **side-by-side** (`xl:flex-row xl:items-center`), stacked below `xl`.

Admin manages the preview source + mode and the slides under `/admin/showcase` (`ShowcaseController`, `ShowcaseSlideController`).

### Database

SQLite for dev (`database/database.sqlite`). For production MySQL, update `.env`:
```
DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

### File Storage

`FILESYSTEM_DISK=local` by default. Uploads (ShowroomItem thumbnails `showcase/`, preview HTML `showcase/previews/`, slide images `showcase/slides/`) are stored on the **`public`** disk via `->store(..., 'public')` and served at `/storage/...` — run `php artisan storage:link` (prod uses the `public_html/storage` symlink). Uploaded files live in `storage/app/public`, which is **gitignored** — they are NOT carried by git-pull deploys, so files uploaded only in dev won't exist in prod (and vice-versa). Views render URLs with `Storage::url($path)`.

### Views

```
resources/views/
  layouts/
    public.blade.php    # public site — fixed dark nav, login modal, footer
    portal.blade.php    # staff/admin/showroom — collapsible dark sidebar
  public/               # home, how-we-work, products, showcase, contact
  auth/                 # login, forgot-password, reset-password
  showroom/             # index (grid), show (full-screen iframe)
  staff/                # dashboard, customers/{index,show}, inquiries/{index,show}
  admin/                # dashboard, users/{index,create,edit}, showcase/{index,slides}, content/index
  components/
    icon.blade.php      # Heroicons wrapper — <x-icon name="..." class="..." />
```

### Design System

Brand colors defined as CSS custom properties in `resources/css/app.css` under `@theme`:

| Token | Value | Use |
|---|---|---|
| `--color-bg` | `#0D1117` | Page background |
| `--color-surface` | `#161C27` | Cards, panels |
| `--color-surface-2` | `#1C2333` | Inputs, hover states |
| `--color-primary` | `#6DBE2E` | Lime green — CTAs, accents |
| `--color-border` | `#1E2A3A` | Borders |
| `--color-text` | `#E8EDF5` | Body text |
| `--color-muted` | `#8B9BB4` | Secondary text |

**Component classes:** `.btn-primary`, `.btn-ghost`, `.btn-danger`, `.btn-sm`, `.card`, `.card-hover`, `.card-glow`, `.input`, `.select`, `.label`, `.badge`, `.badge-green/blue/amber/red/muted`, `.nav-link`, `.sidebar-link`, `.glow-text`, `.gradient-text`, `.wide`, `.data-table`, `.skeleton`

**Animation classes:** `.animate-fade-in-up`, `.animate-float`, `.animate-glow-pulse`, `.delay-{100-500}`, `.opacity-0-init`

### Alpine.js Components (`resources/js/app.js`)

| Component | Usage |
|---|---|
| `loginModal()` | Login popup — `x-data="loginModal()"` on `<body>` in public layout |
| `portalShell()` | Sidebar collapse state — `x-data="portalShell()"` on `<body>` in portal layout |
| `scrollReveal(delay?)` | IntersectionObserver fade-in — `x-data="scrollReveal()"` on section elements |
| `iframeEmbed(url)` | ShowRoom iframe with skeleton loader |
| `confirmDelete(msg)` | Delete confirmation before form submit |
| `flash()` | Auto-dismiss after 5s |
| `tabs(defaultTab)` | Tab switching |

### Controllers

**Public** (`app/Http/Controllers/Public/`): `HomeController`, `HowWeWorkController`, `ProductsController`, `ShowcaseController`, `ContactController`

**Auth** (`app/Http/Controllers/Auth/`): `LoginController`, `ForgotPasswordController`, `ResetPasswordController`

**Showroom**: `ShowroomController` (root namespace)

**Staff** (`app/Http/Controllers/Staff/`): `DashboardController`, `CustomerController`, `InquiryController`

**Admin** (`app/Http/Controllers/Admin/`): `DashboardController`, `UserController`, `ShowcaseController`, `ShowcaseSlideController`, `ContentController`

### Key Files

- `routes/web.php` — all routes
- `bootstrap/app.php` — middleware aliases (`role:`)
- `app/Http/Middleware/EnsureRole.php` — role enforcement
- `app/Models/SiteContent.php` — `get()`/`set()` CMS helpers
- `resources/css/app.css` — full design system
- `resources/js/app.js` — Alpine.js component registrations
- `database/seeders/AdminSeeder.php` — dev seed credentials
