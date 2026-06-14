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

There are no automated tests. Smoke-test over HTTP with PowerShell `Invoke-WebRequest` (`-SessionVariable`, pull the CSRF token from the `csrf-token` meta tag, POST with `_method` spoofing for PATCH/DELETE). PowerShell variables are **case-insensitive** (`$c` == `$C`) — don't collide a loop var with a session var.

## Deployment (production — GoDaddy)

Production is a GoDaddy shared host. `public_html` is the doc root (not Laravel's `public/`); `public_html/build` and `public_html/storage` are **symlinks** into the repo's `public/build` and `storage/app/public`. Production uses **MySQL** (dev is SQLite). Deploys are git-based: push to `origin/main`, then on the server `git pull` + clear/rebuild caches.

**⚠️ Critical: rebuild assets whenever you add a new CSS class.** `public/build/` (compiled Vite/Tailwind output) **is committed to git** and shipped via `git pull`. Locally `npm run dev` generates Tailwind classes on the fly so dev *always* looks right — but production serves the pre-compiled bundle. If a Blade edit introduces **any** new Tailwind class (a new utility, an arbitrary value like `h-[520px]`, or a new responsive variant like `xl:flex-row`) and you don't run `npm run build` + commit `public/build`, those classes **silently have no effect on production**. (Real incident: the `h-[520px]` class wasn't in the prod CSS, so an absolutely-positioned `<img>` collapsed to zero height and showcase images vanished — dev looked fine the whole time.) After building, verify the class is really in the bundle: `grep -o "width:90vw" public/build/assets/app-*.css`.

A deploy is "Blade-only, no build" **only** if it reused already-existing classes. If any class name changed: `npm run build` → `git add public/build` → commit → push. The CSS filename is content-hashed, so browser caches bust automatically (no manual cache-busting needed).

**⚠️ Critical: clear the ROUTE cache whenever you add/rename/remove a route.** Production runs `php artisan route:cache`, so it serves a cached route table. If a deploy adds a route (e.g. a new `route()` name referenced in a Blade view) and you don't rebuild the route cache, the page throws `RouteNotFoundException: Route [x] not defined` even though the route exists in `routes/web.php`. (Real incident: `admin.showcase.reorder` + the `billing.*` routes 500'd on prod until `route:clear`/`route:cache` was run.) Same applies to the config cache when `.env`/config changes.

**Server post-pull steps:**
```bash
git pull origin main
php artisan migrate --force      # only if new migrations
php artisan optimize:clear       # safest: clears view+route+config+cache in one shot
php artisan view:cache
php artisan route:cache           # rebuild route cache — REQUIRED if routes changed
php artisan config:cache
```
When unsure which caches are stale, `php artisan optimize:clear` after the pull is always safe (it just drops all caches; Laravel rebuilds per-request until you re-cache).

The `laravel-deploy-packager` agent runs the push side; tell it explicitly when assets were rebuilt (it assumes "no build needed" otherwise) **and when routes/migrations changed** (so it includes `migrate --force` + `route:clear`/`route:cache` in the server steps).

**Mail (prod):** outgoing mail (agreement/inquiry/work-order notifications) needs a working transport on the server (`relay-hosting.secureserver.net:25` or sendmail). Locally `MAIL_MAILER=log`. Every send is wrapped in try/catch + `Log::error` so a mail failure never blocks a state transition.

## Architecture

### Route Groups (`routes/web.php`)

| Prefix / path | Middleware | Description |
|---|---|---|
| `/` | none | Public pages (home, how-we-work, products, showcase, contact) |
| `/login`, `/forgot-password`, `/reset-password`, `POST /register` | `guest` | Auth + public self-registration |
| `/billing*`, `/profile*` | `auth` | Invoices the customer can view/pay + the account page (**all roles** reach `/profile`) |
| `/dashboard`, `/agreements*`, `/work-orders*`, `/inquiries*` | `auth`, `role:customer` | Customer portal |
| `/showroom/*` | `auth` | Demo portal (all roles) |
| `/staff/*` | `auth`, `role:staff,admin` | Staff portal |
| `/admin/*` | `auth`, `role:admin` | Admin portal |

### Auth

Single `User` model with `role` column: `admin`, `staff`, `customer`.

- `EnsureRole` middleware (`app/Http/Middleware/`) — registered as `role:` alias in `bootstrap/app.php`.
- Login: Alpine modal on public pages → `POST /login` → `LoginController`. Redirects: admin → `/admin/dashboard`, staff → `/staff/dashboard`, **customer → `/dashboard`**.
- Public **self-registration**: `RegisterController@store` creates a `customer`. On register/login, any pre-existing inquiries matching the email are linked to the new account.
- `loginModal()` Alpine component (`resources/js/app.js`) persists email via `$persist` (localStorage `rid_email`).
- **Account menu**: the top-right name/avatar in `layouts/portal.blade.php` is an Alpine dropdown (`x-data="{ open }"`, click-outside/Esc to close) → **My Account** (`/profile`) + **Log out** (`POST /logout`). `ProfileController` is role-agnostic (`auth()->user()`); profile routes live in the bare-`auth` group so staff/admin reach their account too.

### Models

| Model | Table | Notes |
|---|---|---|
| `User` | `users` | role `admin/staff/customer`; helpers `isAdmin/isStaff/isCustomer/isStaffOrAdmin`, `fullAddress()`. Profile/billing cols: `company,phone,website,billing_email,address_line1/2,city,state,postal_code,is_active,last_login_at,notes`. Relations: `invoices/agreements/workOrders/showroomItems/inquiries`. |
| `ShowroomItem` | `showroom_items` | `tech_tags` CSV (`techTagsArray()`); `scopeActive()`; `slides()`. Preview cols `preview_url`+`preview_html_path`+`preview_mode`(`frame`\|`window`); `previewUrl()`/`previewMode()`/`hasPreview()`. |
| `ShowcaseSlide` | `showcase_slides` | belongs to `ShowroomItem`; `bullets` JSON array; `image_path` on `public` disk; `sort_order`. |
| `CustomerShowroomAccess` | `customer_showroom_access` | pivot; `status` (`pending`/`approved`); `granted_by`. |
| `Agreement` | `agreements` | **The quote / statement of work.** status `draft→pending_customer_review→pending_validation→completed` (+`canceled`); `has_cost`, `total_amount`, `deposit_amount`; signature (`signature_method` drawn\|typed, `signature_data`, `signature_name`, `signature_font`); `DEFAULT_BODY`/`PRODUCTION_BODY` template constants. Relations `customer/creator/payments/invoices/workOrder`. Guards `canSend/canCustomerSign/canSubmit/canValidate/canCancel/isLocked`; money `amountPaid/amountPending/balance/depositPaid`; `statusBadgeClass/statusLabel`. |
| `Payment` | `payments` | belongs to `Agreement` (FK required) + payer. `type` deposit/partial/full, `status` pending/confirmed/failed/refunded, `method` manual/stripe/other. **Stripe-ready** (`gateway`,`reference`) but no gateway wired and **no card data is ever stored**. `amountPaid()` counts `confirmed` only. |
| `Invoice` | `invoices` | **The actual bill.** Auto unique `number` (`INV-0001`, `generateNumber()` in a `creating` hook); nullable `agreement_id`; `subtotal`+`tax_rate`%+`tax_amount`+`amount` (grand total); status draft/sent/paid/overdue; `visible_to_customer`; `file_path`. `items()`, `events()`; `recalcTotals()` (subtotal→tax→total), `logEvent()`, `isOverdue()`, `statusBadgeClass()`. |
| `InvoiceItem` | `invoice_items` | `description,quantity,unit_price,sort_order`; `lineTotal()`. |
| `InvoiceEvent` | `invoice_events` | audit trail — `action,description,meta(json),user_id`; `actor()`. |
| `WorkOrder` | `work_orders` | status incl `completed/canceled` (+`awaiting_customer_validation`); `customer()`, `notes()`, `events()`, `agreements()`; `awaitingCustomer()/customerValidated()`, `statusBadgeClass/statusLabel`, `lastCustomerVisibleNote()`. |
| `WorkOrderNote` | `work_order_notes` | threaded notes; `visible_to_customer`, `read_at` (unread-from-customer badge). |
| `WorkOrderEvent` | `work_order_events` | audit trail (`logEvent`). |
| `Inquiry` | `inquiries` | status `new/in_progress/resolved`; nullable `user_id` (guest contact-form or linked account); `notes()` threaded; `statusBadgeClass/statusLabel`. |
| `InquiryNote` | `inquiry_notes` | threaded notes; `visible_to_customer` (customer-visible vs internal). |
| `CustomerNote` / `CustomerFile` | `customer_notes` / `customer_files` | admin notes + file uploads attached to a customer. |
| `Prospect` | `prospects` | OSM lead-gen. `STATUSES = new,shortlisted,contacted,ruled_out,won`; `presence_score`, `scan_data` (website scan), `notes()`. |
| `ProspectNote` / `ProspectSearchArea` | — | prospect notes + saved map search areas. |
| `SiteContent` | `site_contents` | key-value CMS; `SiteContent::get($key,$default)` / `set($key,$value)`. |

**Audit-trail pattern:** event tables (`invoice_events`, `work_order_events`) + a `logEvent($action, $description, $userId = null, $meta = null)` method on the parent model. The invoice editor and work-order page render these as a timeline (`events()` is `->latest()`). Agreements/inquiries use lifecycle timestamp columns instead (`sent_at`, `submitted_at`, …).

### Customer Portal & Dashboard

- **`/dashboard`** (`DashboardController`) is the customer landing page. Brand-new customers (no agreements/work orders) get an **onboarding** view: welcome + ShowRoom cards + "start a project" CTA → inquiry form. Established customers get an action-needed banner, stat tiles, an **Active Invoices** section (unpaid), an **Active Work Orders** section, and a recent-agreements table. List rows are clickable (`onclick="window.location.href=…"`).
- Sidebar (customer): Dashboard, Agreements, Work Orders, Billing, ShowRoom, Inquiries, My Account. Sidebar badges (action counts) are shared via `AppServiceProvider`'s `View::composer('layouts.portal', …)`.

### Agreements & Payments

Admin drafts an agreement (body prefilled from `SiteContent` `agreement_default_text`/`agreement_production_text`, two templates), sends it → customer reviews (scroll-gated), **signs** (drawn canvas or typed cursive — `agreementReview()` Alpine component), optionally pays a deposit, and submits → admin validates & completes. Payments are recorded manually (pending → admin confirms). Staff editor: `resources/views/staff/agreements/edit.blade.php`; customer view: `resources/views/agreements/{index,show}.blade.php`; PDF: `resources/views/agreements/pdf.blade.php`. An agreement can be converted to / attached to a `WorkOrder`.

### Invoicing

**Agreement = the quote; Invoice = the actual bill used to collect/record payment.**

- **Create from an agreement** (`InvoiceController@storeFromAgreement`, button on the agreement editor): seeds one line item priced at the agreement's `total_amount` + the site **default tax rate**, then opens the editor. Quick-create from the customer page also works (`store`).
- **Invoice editor** (`staff.customers.invoices.edit`): details + repeatable **line items** (inline Alpine, live subtotal/tax/total) + tax rate + **audit trail** timeline. `update` replaces items wholesale, calls `recalcTotals()`, logs a `status` event on status change + an `updated` event.
- **Unique number** auto-assigned (`INV-0001`); **due date** defaults to +1 week; **default tax rate** is `SiteContent` key `default_tax_rate` (set under Admin → Site Content).
- **Staff/admin Invoices section**: `staff.invoices.index` (`/staff/invoices`) — all invoices across customers with **Active/Inactive pills** (Active = unpaid, default), clickable rows → editor.
- **PDF** (`resources/views/invoices/pdf.blade.php`, dompdf): itemized lines + subtotal + tax + total. When rendered for the **customer** (`CustomerInvoiceController@pdf` passes `includeAgreement = true`) the attached agreement is appended as a **second page** (`page-break-before`).
- **Customer billing**: `/billing` (index, Active/Inactive pills), `/billing/{invoice}` (`billing.show` — view bill + **submit payment**), `/billing/{invoice}/pdf`, `POST /billing/{invoice}/payment` (records a `Payment` against the invoice's agreement as `pending`; admin confirms it from the agreement editor — reuses the one payment ledger, so no schema dup).

### Admin Dashboard (`admin/dashboard.blade.php`)

Six stat cards on one row (`grid-cols-2 md:grid-cols-3 xl:grid-cols-6`): **Active Agreements**, **Active Work Orders**, **Active Inquiries**, **Shortlisted Prospects** (counts), **Unpaid Invoices** ($ of sent+overdue), and **Collected** ($ confirmed payments, with a YTD/All Alpine pill toggle — both values server-rendered, swapped client-side; YTD filters `whereYear('paid_at', now()->year)`, which is cross-DB). Every card deep-links to its section **with the right filter pre-applied** (e.g. `/staff/agreements?status=active`, `/admin/prospects?status=shortlisted`, `/staff/invoices`). Below: list sections (active inquiries / work orders / invoices) with clickable rows.

### List filtering conventions

- **Staff index pages** filter **server-side** via query param: agreements/work-orders use `?status=active|completed|canceled|…`, inquiries use `?filter=active|inactive`. The selected pill is highlighted server-side.
- **Customer list pages** (`agreements`, `billing`, `work-orders`, `inquiries` index) and the **staff Invoices index** filter **client-side** with an inline Alpine **Active/Inactive pill** component (`x-data="{ filter: 'active' }"`, default Active, per-row `x-show` + `x-cloak` to avoid flash). Active counts shown on the pills.
- **Prospects map** (`prospectsMap` in `resources/js/prospectsMap.js`) filters client-side; it reads `?status=` on init to deep-link a filter and **defaults to only `new`**.

### Public Showcase (`resources/views/public/showcase.blade.php`)

Cards are a **responsive grid** inside a 90vw centered container (`width: 90vw; margin-left: calc(50% - 45vw)`), always **3 per row** (`grid-cols-3`), cards `w-full h-[500px]`. Each card = preview image background (`object-cover object-top`) + a **frosted, translucent bottom banner** (`rgba(13,17,23,.7)` + `backdrop-blur`) with title, description, and tech-tag chips; hover lifts + primary border.

⚠️ **Alpine `:style` gotcha:** the 90vw width must live on a *clean* element. Alpine's `:style` in **string** form *replaces* the inline `style` attribute, so a static `width:90vw` on the same element as a `:style` fly-out binding gets clobbered (the grid then falls back to `.wide`'s `max-width:1280px` and stops scaling). Keep static width on one element and the `:style` animation on an inner wrapper.

Clicking a card opens the preview engine (a single Alpine `x-data`, driven per-item by `previewJson`):
- **`frame` mode** → fixed 90vw overlay with a sandboxed `<iframe>` (skeleton, open-in-new-tab, Esc/click-out). External sites sending `X-Frame-Options`/CSP `frame-ancestors` can't embed — use `window` mode.
- **`window` mode** → opens the URL in a new tab.
- **No preview** → image **slideshow** panel (`$item->slides`): auto-advancing tabs, lightbox with arrow/keyboard cycling, dots/progress. Also breaks out to 90vw; on `xl`+ image (`h-[520px]`) + text sit side-by-side.

Admin manages preview source/mode + slides under `/admin/showcase` (`ShowcaseController`, `ShowcaseSlideController`; slides reorder via the `sortable` Alpine component).

### Prospects (`/admin/prospects`)

Leaflet map (lazy-imported in `prospectsMap.js`) of businesses pulled from OpenStreetMap via `ProspectController@search` (Overpass). Statuses pipeline (`new`→`shortlisted`→…); `WebsiteScanService` crawls a prospect's site for contacts; CSV export. Data loads from `/admin/prospects/data` (JSON).

### Database

SQLite for dev (`database/database.sqlite`). Production MySQL via `.env` (`DB_CONNECTION=mysql`, host/port/database/username/password). 36 migrations under `database/migrations` (dated `2026_06_13_*`); newest add invoice tax/totals, `invoice_items`, `invoice_events`.

### File Storage

`FILESYSTEM_DISK=local`. Uploads use the **`public`** disk via `->store(..., 'public')`, served at `/storage/...` (`php artisan storage:link`; prod uses the `public_html/storage` symlink). Paths: ShowroomItem thumbnails `showcase/`, preview HTML `showcase/previews/`, slide images `showcase/slides/`, invoice PDFs `customers/{id}/invoices/`, customer files `customers/{id}/files/`. `storage/app/public` is **gitignored** — uploaded files are NOT carried by git-pull deploys (dev uploads won't exist in prod and vice-versa). Render URLs with `Storage::url($path)`. Drawn signatures are stored inline as base64 data-URIs (not files).

### Views

```
resources/views/
  layouts/      public.blade.php (public nav/login modal/footer) · portal.blade.php (sidebar + account-menu dropdown)
  public/       home, how-we-work, products, showcase, contact
  auth/         login, forgot-password, reset-password
  dashboard/    index            # customer dashboard (onboarding + active sections)
  agreements/   index, show, pdf # customer-facing agreement review + sign + pay
  work-orders/  index, show      # customer work-order tracking
  billing/      index, show      # customer invoices: list + view/pay
  invoices/     pdf              # dompdf invoice (customer copy appends the agreement)
  inquiries/    index, show      # customer inquiry submit + thread
  profile/      edit             # account page (all roles)
  showroom/     index, show
  staff/        dashboard, customers/{index,show,_invoice_fields}, agreements/edit,
                work-orders/{index,edit}, invoices/{index,edit}, inquiries/{index,show}
  admin/        dashboard, users/{index,create,edit}, showcase/{index,slides}, content/index, prospects/index
  emails/       *.blade.php       # HTML notification emails
  components/   icon.blade.php    # Heroicons wrapper — <x-icon name="..." class="..." />
```

### Design System

Brand colors as CSS custom properties in `resources/css/app.css` under `@theme`:

| Token | Value | Use |
|---|---|---|
| `--color-bg` | `#0D1117` | Page background |
| `--color-surface` | `#161C27` | Cards, panels |
| `--color-surface-2` | `#1C2333` | Inputs, hover states |
| `--color-primary` | `#6DBE2E` | Lime green — CTAs, accents |
| `--color-primary-glow` | — | translucent primary (icon chips) |
| `--color-border` | `#1E2A3A` | Borders |
| `--color-text` | `#E8EDF5` | Body text |
| `--color-muted` | `#8B9BB4` | Secondary text |

Theme colors are also usable as canonical Tailwind utilities (`bg-surface-2`, `text-primary`, `border-primary/30`, etc.) in addition to the `[var(--color-x)]` arbitrary form — both compile; older markup uses the arbitrary form.

**Component classes:** `.btn-primary/-ghost/-danger/-sm`, `.card`, `.card-hover`, `.card-glow`, `.input`, `.select`, `.label`, `.badge`, `.badge-green/blue/amber/red/muted`, `.nav-link`, `.sidebar-link`, `.glow-text`, `.gradient-text`, `.wide` (`max-width:1280px` centered), `.data-table`, `.skeleton`, `.no-scrollbar` (hide scrollbar, keep scroll). **Animations:** `.animate-fade-in-up/-fade-in/-float/-glow-pulse/-slide-right`, `.delay-{100-500}`, `.opacity-0-init`. Cursive Google Fonts (Dancing Script, Great Vibes, Pacifico, Satisfy) are linked in the portal `<head>` for typed signatures.

### Alpine.js Components (`resources/js/app.js`)

| Component | Usage |
|---|---|
| `loginModal()` | Login popup (public layout) |
| `portalShell()` | Sidebar collapse state (portal layout) |
| `scrollReveal(delay?)` | IntersectionObserver fade-in |
| `iframeEmbed(url)` | ShowRoom iframe + skeleton |
| `confirmDelete(msg)` | Confirm before form submit |
| `flash()` | Auto-dismiss flash after 5s |
| `tabs(defaultTab)` | Tab switching |
| `sortable(reorderUrl)` | Drag-reorder (SortableJS) — showcase slides |
| `agreementReview(defaultName?)` | Agreement review: scroll-gate, signature pad (drawn `<canvas>` + typed cursive), payment |
| `prospectsMap(config)` | Leaflet prospects map (own file `prospectsMap.js`, lazy import; reads `?status=`, defaults to `new`) |

Plus **inline `x-data`** patterns (not registered): Active/Inactive filter pills on list pages, the showcase preview/slideshow engine, the header account-menu dropdown, and the invoice-editor line-items repeater.

### Controllers

- **Public** (`Public/`): `HomeController`, `HowWeWorkController`, `ProductsController`, `ShowcaseController`, `ContactController`
- **Auth** (`Auth/`): `LoginController`, `RegisterController`, `ForgotPasswordController`, `ResetPasswordController`
- **Customer / root namespace**: `DashboardController`, `CustomerAgreementController`, `CustomerWorkOrderController`, `CustomerInvoiceController`, `CustomerInquiryController`, `ProfileController`, `ShowroomController`
- **Staff** (`Staff/`): `DashboardController`, `CustomerController`, `CustomerNoteController`, `CustomerFileController`, `AgreementController`, `WorkOrderController`, `InvoiceController`, `InquiryController`
- **Admin** (`Admin/`): `DashboardController`, `UserController`, `ShowcaseController`, `ShowcaseSlideController`, `ContentController`, `ProspectController`

### Mailables (`app/Mail/`)

`AccessRequested`, `AccessApproved`, `WelcomeEmail`, `InquiryConfirmation`, `InquiryReply`, `NewInquiryNotification`, `AgreementSent`, `AgreementSubmitted`, `AgreementCompleted`, `WorkOrderValidationRequested`, `WorkOrderValidated`, `WorkOrderCustomerMessage`. envelope()/content() API; HTML blades in `resources/views/emails/`; always dispatched in try/catch + `Log::error`.

### Site Content keys (`SiteContent`)

Editable under Admin → Site Content: `hero_headline`, `hero_subheadline`, `about_text`, `contact_intro`, `agreement_default_text`, `agreement_production_text`, `default_tax_rate`. Also read by PDFs/footer (set directly): `company_name`, `contact_email`, `contact_phone`.

### Key Files

- `routes/web.php` — all routes
- `bootstrap/app.php` — middleware aliases (`role:`)
- `app/Http/Middleware/EnsureRole.php` — role enforcement
- `app/Providers/AppServiceProvider.php` — sidebar badge counts via `View::composer('layouts.portal')`
- `app/Models/SiteContent.php` — `get()`/`set()` CMS helpers
- `resources/css/app.css` — full design system
- `resources/js/app.js` + `resources/js/prospectsMap.js` — Alpine components
- `database/seeders/` — `AdminSeeder` (dev credentials) + demo data seeders
```
