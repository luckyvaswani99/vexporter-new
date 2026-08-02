# VEXPORTER — Multivendor B2B E-Commerce Platform

**Tagline:** *Where The World Trades*
**Niche:** Pharma + Solar (+ Main Store: electronics, textiles, machinery, general merchandise)
**Repo:** `C:\Users\Lucky\Herd\vexporter-` · **Local URL:** `http://vexporter-.test`
**Design source:** `C:\Users\Lucky\OneDrive\Desktop\vexporter\index.html` (static Tailwind CDN homepage — poora design system + homepage layout wahi se aayega)

---

## Build status (live)

| Phase | Status | Notes |
|---|---|---|
| CodeGraph index | ✅ done | `.codegraph/` initialised — 31 files, 101 nodes indexed |
| 0 — Setup | ✅ done | PHP 8.4.20 (Herd `php84.bat`), Composer 2.9.5, **Filament v4.12.4 Laravel 13 pe verified**, Spatie permission/medialibrary/activitylog/sluggable installed, SQLite dev DB, migrations green, Pest suite green (7/7) |
| 1 — Design system | 🔄 mostly done | Tokens, utilities, Alpine core, ~25 Blade components aur **homepage complete** — design se 1:1. Baaki: PLP/PDP/cart/form components |
| 2 — Data layer | ✅ done | **60+ tables** (9 migration groups), **34 models** with relations/scopes/accessors, 5 factories, 5 seeders. Homepage ab poori tarah DB-driven hai — `DemoCatalog` delete. Money integer minor units + `App\Support\Money`. Demo data: 3 verticals, 18 categories, 31 attributes, 12 vendors (10 approved + 2 pending), 116 products (8 hero + depth + 4 pending approval), tier prices, 40 orders → sub-orders/items/payments/ledger/shipments |
| 3 — Auth + vendor onboarding | ✅ done | Login/register (buyer/vendor toggle)/forgot+reset password/email verification, **5-step vendor wizard** (company → statutory → catalogue → certifications+uploads → payout), pending → approve/reject actions with notifications + KYC audit log, spatie roles/permissions (5 roles, 16 permissions), 4 policies (Vendor/Product/SubOrder/Order), buyer dashboard, `/become-a-vendor` landing |
| 5 — Filament panels | ✅ done | **Admin panel** `/admin` (14 resources, 6 nav groups, 3 widgets: GMV stats + pending-vendor queue + 12-week revenue chart, vendor approve/reject + KYC document verify/download, product approve/reject + bulk approve, payout mark-paid) · **Vendor panel** `/vendor/store/{slug}` (multi-tenant, products with auto "pending review" on create, order fulfilment accept→process→ship→deliver with shipment + status history, payouts read-only, store profile page) |
| 4 — Storefront | ✅ done | PLP (vertical/category/search/deals/new-arrivals) with facets + sort + mobile filter drawer, PDP (tier prices, MOQ stepper, specs/documents/reviews tabs, JSON-LD, pharma licence gating), vendor directory + store pages, guest cart (session) → checkout → order split per vendor, account (orders, order timeline, wishlist, RFQs), aur **poora RFQ loop**: buyer request → vendor invites → vendor quote builder (Filament, line items + terms + revise/withdraw) → buyer compare & accept → confirmed order |
| 6 — Payments & Payouts | ✅ done | **Razorpay + Stripe + Bank Wire (T/T)** gateway abstraction (`PaymentGateway`, `RazorpayGateway`, `StripeGateway`, `BankTransferGateway`, `PaymentManager`), escrow hold/release engine (`EscrowService`), webhook idempotency (`webhook_events` table, signature verification), payout batching & CSV export (`PayoutService`), double-entry ledger bookkeeping, storefront payment selection view, proforma invoice view, aur Filament Admin (Payments, Payouts, Ledger) + Vendor (Payouts) resources. |
| 7 — Logistics, Docs & Compliance | ✅ done | **Freight Estimation & Tracking** (`ShippingService`, `ShipmentService`, public `/track-order`), **Export Document PDFs** (`DocumentGenerator`, Commercial Invoice with LUT zero-rated export declaration, Packing List, Certificate of Origin), **Compliance Guardrails** (`ComplianceService` for Pharma drug license & COA requirements, Solar BIS/ALMM warranty checks, `LegalPageSeeder`), aur **Disputes/RMA Loop** (`DisputeController`, dispute thread, escrow hold freeze, Filament Admin `DisputesResource`). |
| 8 — Search, Performance & SEO | ✅ done | **Search Engine & Synonyms** (`SearchService` with industry term expansion `API`↔`Active Pharmaceutical Ingredient`, `PV`↔`Solar Panel`, autocomplete `SuggestController`), **Observer Cache Invalidation** (`ProductObserver`, `VendorObserver`, `CategoryObserver`), **SEO & OpenGraph** (`SeoService`, `<x-seo.meta>`), **Schema.org Structured Data** (`JsonLdGenerator` for `Organization`, `Product`, `Offer`, `AggregateRating`, `BreadcrumbList`), aur **Dynamic Sitemap/Robots** (`SitemapController`, `/sitemap.xml`, `/robots.txt`). |
| 9 — Testing, Quality & Security | ✅ done | **131 Pest tests** (unit + feature + security), **PHPStan level 3 clean** (Larastan + generated model annotations), Pint clean, `composer audit` clean, `composer check` gate. Hardening: security headers + CSP middleware, `AuthenticateSession` on the web group, **private document streaming** (KYC/COA never served from the public disk), **activity-log audit trail** on vendor / product / sub-order / payout, **admin 2FA** (TOTP + recovery codes, encrypted at rest). Real bugs fixed: `env()` reads inside the payment gateways (return null once `config:cache` runs in production), broken JSON-LD product image, dead branch in escrow release. |
| 10 — Deploy | ✅ done (repo side) | [`DEPLOYMENT.md`](DEPLOYMENT.md) runbook (server spec, first deploy, rollback, workers, scheduler, webhooks, backups, monitoring, go-live checklist), [`deploy.sh`](deploy.sh), [`.env.production.example`](.env.production.example), **GitHub Actions CI** (Pint + PHPStan + `composer audit` + asset build + tests), scheduled tasks wired in `routes/console.php` (FX sync, hourly escrow release, weekly payout batch, log/queue housekeeping), production guardrails (`URL::forceScheme('https')`, `DB::prohibitDestructiveCommands()`, trusted proxies), and **`php artisan vexporter:preflight`** — 13 deploy-blocking checks with `--strict`. Server provisioning itself is a client action (needs hosting credentials). |
| 11 — Homepage CMS | ✅ done | **Admin → Content → Homepage** (`ManageHomepage`, gated on `content.manage`): section order + visibility (drag to reorder, toggle to hide), hero copy/buttons/floating tiles, trust badges, category & vertical showcase headings with per-section product counts, vendor strip, "Why us" selling points, testimonials heading, vendor CTA + bullets, newsletter, and homepage meta title/description. Backed by `site_settings` (one JSON row per group, day-long cache flushed on save) with `App\Support\Homepage` holding the shipped copy as defaults — an empty table renders exactly the approved design, and fields added in a later release appear automatically. Colours come from a literal tone map so Tailwind generates every class. |
| 11b — Header & footer CMS | ✅ done | **Admin → Content → Header & Footer** (`ManageSiteChrome`, same gate): top-bar on/off with phone, email and its links; search-box placeholder; wishlist shortcut toggle (header **and** mobile menu); footer about text, social links, fully rebuildable link columns (nested repeater), copyright line with `:year`, legal links and payment icons. Defaults in `App\Support\SiteChrome`, resolved from named routes so they survive a URL change. `config('vexporter.socials')` retired; `contact.*` stays as the fallback and still feeds the Organization JSON-LD and emails. Shared `SettingsPage` base class carries load/save/cache-flush for both pages. |
| 11c — Branding, rich text & variants | ✅ done | **Branding tab**: logo upload for light/dark backgrounds + favicon (public disk), tagline toggle — `<x-brand.logo>` prefers the upload, then `public/images/brand`, then the CSS mark. **Rich text**: `RichEditor` for product short + full description (admin **and** vendor panels) and category description, rendered through a new `prose-storefront` utility (Tailwind v4 has no typography plugin, so the old `prose` class was a no-op). Output is sanitised in `App\Support\Html` — DOM-based tag/attribute allow-list applied in model mutators, so the vendor panel cannot inject script; meta descriptions and JSON-LD get the flattened text. **Product type**: now a radio group with a description per option, and `variable` finally does something — a Variants repeater (name/SKU/price/stock/default) appears when it is selected, the PDP renders an option picker, and the choice flows through `priceForQty` → cart line → order item. A variable product cannot be added to the cart without an option; a variant id sent for a simple product is ignored. |

| 11d — Payment methods CMS | ✅ done | **Admin → Finance → Payment methods** (`ManagePaymentMethods`, gated on `settings.manage`): enable/disable and reorder Razorpay / Stripe / Bank wire, edit the label, blurb, icon and reassurance copy for each, and enter the wire-transfer beneficiary details. Per-gateway credential status is reported read-only — API keys stay in the environment and are never written to the database. The payment page and checkout page both render from these settings, `payment.process` and `payment.complete` validate the gateway against the enabled list (a disabled method is not chargeable from a stale page), and the proforma invoice prints the same details. Preflight gains "no method enabled" (fail) and "wire offered with no account set" (warn). |

### Regression caught and fixed here

Making `<x-home.vertical-showcase>` read its copy from a settings group broke the two literal-prop call sites on the product page (related products, more-from-vendor) — every product page with neighbours returned 500 while the test suite stayed green, because the fixtures had no neighbours. The component now accepts either a group or explicit props, and a test creates neighbours so the path is covered.

**Placeholder bank account removed.** The payment page and proforma invoice both printed a hard-coded beneficiary ("VEXPORTER GLOBAL LTD", HDFC, account 50200012345678). A buyer could have wired money to an account that does not exist. Wire details now come from the admin page and default to blank; with nothing set, both surfaces say the details will be emailed instead.

### Phase 9 follow-ups (tracked, not blocking)

- PHPStan is enforced at **level 3** (0 errors). Level 5 still reports ~40 items, almost all `nullsafe.neverNull` — defensive `?->` on relations the generated annotations now type as non-null. Removing them is mechanical but trades runtime resilience for static purity inside payment/order paths, so it is deliberately deferred rather than done blind.
- Admin 2FA is **optional** today (`isRequired: false` in `AdminPanelProvider`). Flip it to `true` once the team has enrolled.
- Buyer-facing GDPR data export/delete is still to build.
- Rate limiting covers login, RFQ, newsletter and search suggest; cart/wishlist endpoints are session-bound but unthrottled.

**Deferred (locally unavailable, plan me bane hue hain):** Redis (cache/queue → `database` driver abhi), Meilisearch + Scout (Phase 8), Horizon (Phase 8).

**Pending from client:** official logo file (`public/images/brand/logo-dark.svg|png` + `logo-light.svg|png`) — `<x-brand.logo>` file milte hi automatically use kar lega, tab tak design wala CSS "V" mark fallback hai.

---

## 0. Decisions (locked)

| Decision | Choice | Note |
|---|---|---|
| Storefront stack | **Pure Blade + Alpine.js** | No Livewire. Interactivity Alpine + `fetch()` se, JSON endpoints backing. |
| Admin/Vendor dashboards | **Filament 4 — 2 panels** (`/admin`, `/vendor`) | Ready-made CRUD, approvals, orders, payouts. |
| Commerce model | **Hybrid: Cart + RFQ** | Normal SKUs pe add-to-cart/checkout; bulk/EPC/API items pe "Get Quote" (RFQ → Quote → Order). |
| Payments | **Razorpay + Stripe dono** | Gateway buyer country/currency ke hisaab se. Driver-based abstraction. |
| Database | **SQLite (dev) → MySQL 8 (prod)** | Local machine pe MySQL available nahi hai (Herd free me service nahi aati). Migrations MySQL-compatible likhi ja rahi hain; switch sirf `.env` change hai. |
| Base currency | **USD** (display), settlement INR | Multi-currency with daily FX rates. |
| Money storage | **BIGINT minor units** (cents) + currency code | Kabhi float nahi. |

> Assumption (flag kar raha hoon): design me sirf homepage hai — baaki 40+ pages (PLP, PDP, cart, checkout, vendor store, account, RFQ) isi design language me **hum banayenge**, tokens/components reuse karke. Agar aur mockups hain to Phase 2 se pehle share karna.

---

## 1. Tech Stack

**Backend**
- PHP 8.3+ (Herd: `C:\Users\Lucky\.config\herd\bin\php84.bat` — `php` PATH me nahi hai, scripts me full path use karna)
- Laravel 13.x, Laravel Tinker, Laravel Boost (already installed)
- MySQL 8, Redis (cache + queue + session; Herd/Memurai), Meilisearch (search)

**Frontend**
- Blade components + Alpine.js 3 + Tailwind CSS v4 (Vite 8)
- Font Awesome 6 (self-hosted via npm, CDN nahi)
- Fonts: Inter (body) + Poppins (display) — Vite `bunny()` plugin se self-host

**Packages (composer)**
| Package | Kyun |
|---|---|
| `filament/filament ^4` | Admin + Vendor panels |
| `spatie/laravel-permission` | Roles: admin, vendor, staff, buyer |
| `spatie/laravel-medialibrary` | Product/vendor/certificate images, conversions |
| `spatie/laravel-activitylog` | Audit trail (compliance ke liye must) |
| `spatie/laravel-sluggable` | SEO slugs |
| `spatie/laravel-sitemap` | sitemap.xml |
| `laravel/scout` + `meilisearch/meilisearch-php` | Product search, facets, typo-tolerance |
| `razorpay/razorpay` | Razorpay SDK |
| `stripe/stripe-php` | Stripe SDK |
| `barryvdh/laravel-dompdf` | Invoice / Proforma / Packing list PDF |
| `maatwebsite/excel` | Bulk product import/export (vendors ke liye critical) |
| `laravel/sanctum` | API tokens (future mobile app / vendor API) |
| `laravel/horizon` | Queue monitoring |
| `propaganistas/laravel-phone` | Intl phone validation |
| `pestphp/pest` (dev, installed) | Testing |
| `larastan/larastan` (dev) | Static analysis level 6 |

**Packages (npm)**
`alpinejs`, `@alpinejs/collapse`, `@alpinejs/focus`, `@alpinejs/persist` (cart state), `@fortawesome/fontawesome-free`, `embla-carousel` (product sliders), `nouislider` (price filter), `photoswipe` (PDP gallery), `axios`.

---

## 2. Phase 0 — Environment & Foundation

**Tasks**
1. `.env` update: `APP_NAME=VEXPORTER`, `DB_CONNECTION=mysql`, `DB_DATABASE=vexporter`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`, `SCOUT_DRIVER=meilisearch`, `FILESYSTEM_DISK=public`.
2. Herd me MySQL DB `vexporter` create; `php artisan migrate`.
3. `.env.example` sync + naye keys: `RAZORPAY_KEY/SECRET/WEBHOOK_SECRET`, `STRIPE_KEY/SECRET/WEBHOOK_SECRET`, `MEILISEARCH_HOST/KEY`, `PLATFORM_COMMISSION_PERCENT=5`, `DEFAULT_CURRENCY=USD`.
4. `phpunit.xml` — test DB SQLite in-memory rakho (fast) + ek MySQL group heavy tests ke liye.
5. Larastan + Pint config; `composer lint` / `composer analyse` scripts.
6. `.editorconfig` already hai — chhedna nahi.
7. Git: `main` protected mindset — har phase apni branch (`feat/phase-1-design-system` …), squash merge.

**Acceptance:** `php artisan migrate:fresh` clean chale, `npm run build` pass, homepage placeholder render ho.

---

## 3. Phase 1 — Design System Port (design ko code me lana)

Ye phase sabse pehle isliye ki baaki sab pages inhi tokens/components se banenge.

### 3.1 Tailwind v4 tokens → `resources/css/app.css`
Design ke CDN `tailwind.config` ko v4 CSS-first `@theme` me convert karna:

```css
@theme {
  --color-brand-red:    #E31837;
  --color-brand-red-dk: #C41230;   /* hover gradient end */
  --color-brand-dark:   #1A1A2E;
  --color-brand-gray:   #6B7280;
  --color-brand-light:  #F8F9FC;
  --color-brand-accent: #FF6B35;
  --font-sans:    'Inter', ui-sans-serif, system-ui, sans-serif;
  --font-display: 'Poppins', ui-sans-serif, system-ui, sans-serif;
}
```

Custom utilities (`@utility` / plain CSS) — design ke `<style>` block se 1:1:
`.gradient-hero`, `.gradient-pharma`, `.gradient-solar`, `.glass`, `.card-hover`, `.animate-float` + `@keyframes float`, `.category-card`, `.product-img`, `.vendor-card`, `.scroll-hidden`, `.btn-primary`, `.section-reveal`.

### 3.2 Logo
Design me logo = red rounded-square me **"V"** + wordmark **VEXPORTER** + tagline. Isko reusable banao:
- `resources/views/components/brand/logo.blade.php` — props: `size` (sm/md/lg), `variant` (dark/light), `withTagline` (bool).
- `public/favicon.svg` + `public/images/logo.svg` (same V mark, vector me redraw) + PNG 512/192 PWA manifest ke liye.
- OG image template (1200×630) — brand dark gradient + logo.

### 3.3 Blade component library (`resources/views/components/`)

```
layouts/
  storefront.blade.php      # topbar + header + category nav + footer + slots
  account.blade.php         # buyer dashboard shell (sidebar)
  bare.blade.php            # checkout / auth (minimal chrome, no distractions)
brand/logo.blade.php
nav/
  topbar.blade.php          # phone, email, Sell on VEXPORTER, Track Order, Help
  header.blade.php          # logo + search + wishlist/account/cart (Alpine dropdowns)
  category-nav.blade.php    # All Categories mega-menu + vertical links + Flash Sale
  mobile-menu.blade.php     # Alpine x-show/x-collapse
  breadcrumbs.blade.php
search/
  bar.blade.php             # input + category select + submit (Alpine autocomplete)
  autocomplete.blade.php    # dropdown results (Meilisearch JSON endpoint)
product/
  card.blade.php            # design ka exact product card (badge, wishlist, hover Add-to-Cart, vendor row, cert chip)
  card-skeleton.blade.php
  price.blade.php           # price + strike + unit (/kg, /unit, /set, turnkey)
  rating.blade.php          # stars + count
  badge.blade.php           # -15%, Bestseller, EPC, New
  cert-chip.blade.php       # FDA / WHO-GMP / CE / ISO / MNRE / BIS / IEC / ALMM
  gallery.blade.php         # PDP images + zoom
  quantity-input.blade.php  # MOQ-aware stepper
  tier-price-table.blade.php# bulk slabs
vendor/
  card.blade.php            # design ka vendor card (avatar letter, tags, products, rating, certs, View Store)
  banner.blade.php          # vendor store header
  trust-badges.blade.php
ui/
  button.blade.php          # primary(gradient)/outline/ghost/white, sizes, icon slot
  section-heading.blade.php # pill + h3 + sub (section-reveal ke saath)
  pill.blade.php
  card.blade.php
  input.blade.php / select.blade.php / textarea.blade.php / checkbox.blade.php / file-upload.blade.php
  alert.blade.php  modal.blade.php (Alpine + focus trap)  drawer.blade.php (cart/filters)
  tabs.blade.php   accordion.blade.php  pagination.blade.php  empty-state.blade.php
  countdown.blade.php       # flash sale timer (Alpine, server se end_at)
  stat.blade.php            # 50K+ Products style
home/
  hero.blade.php  trust-strip.blade.php  category-trio.blade.php
  vertical-showcase.blade.php  deal-banner.blade.php  vendor-strip.blade.php
  why-us.blade.php  analytics-card.blade.php  testimonials.blade.php
  vendor-cta.blade.php  newsletter.blade.php
footer/main.blade.php
```

### 3.4 Alpine setup (`resources/js/app.js`)
- Alpine + plugins register.
- Global stores: `$store.cart` (items, count, total; persist + server sync), `$store.wishlist`, `$store.ui` (mobileMenu, cartDrawer, filterDrawer, searchOpen), `$store.currency`.
- Reusable `Alpine.data()` components: `searchAutocomplete`, `countdown`, `productGallery`, `quantityStepper`, `filterPanel`, `addToCart`, `rfqForm`, `toast`.
- `IntersectionObserver` scroll-reveal (design ka `section-reveal`) ko ek small module me.
- Smooth-scroll design wala hataana — real routes ab hain, sirf on-page anchors pe rakho.

**Acceptance:** ek `/design-system` route (local-only) jahan saare components render hote hain; homepage design ke screenshot se pixel-close.

---

## 4. Phase 2 — Domain Model & Database

### 4.1 Core identity
```
users                id, name, email, phone, password, avatar, locale, default_currency,
                     type(enum: buyer|vendor|admin), is_active, last_login_at, timestamps
roles/permissions    (spatie)  admin, vendor_owner, vendor_staff, buyer, support
addresses            id, user_id, label, company, line1, line2, city, state, postcode,
                     country_code, phone, tax_id, is_default_billing, is_default_shipping
buyer_profiles       user_id, company_name, business_type, gst_number, iec_code,
                     import_license_no, annual_volume, verified_at
```

### 4.2 Vendors
```
vendors              id, user_id(owner), name, slug, legal_name, logo, banner, about,
                     country_code, city, state, gst_number, pan, iec_code, cin,
                     status(enum: pending|approved|suspended|rejected), approved_at, approved_by,
                     commission_percent(nullable → platform default), rating_cache,
                     products_count_cache, response_time_hours, min_order_value,
                     payout_method, payout_details(encrypted json), meta json, timestamps
vendor_verticals     vendor_id, vertical_id           # pharma / solar / main
vendor_users         vendor_id, user_id, role(owner|manager|staff)
vendor_documents     vendor_id, type(gst|pan|iec|factory_license|drug_license|bis|iso|
                     who_gmp|fda|eu_gmp|mnre|almm|ce|rohs|oeko_tex|gots|other),
                     number, issuing_authority, issued_at, expires_at, file_path,
                     status(pending|verified|rejected), reviewed_by, review_note
vendor_bank_accounts vendor_id, account_holder, account_no(encrypted), ifsc/swift, bank, branch,
                     currency, is_primary, verified_at
vendor_kyc_logs      vendor_id, action, actor_id, note, created_at
```

### 4.3 Catalog
```
verticals            id, name(Main Store|Pharma|Solar), slug, icon, gradient_class,
                     description, products_count_cache, sort_order
categories           id, vertical_id, parent_id (nested set / adjacency + path),
                     name, slug, icon, image, description, seo_title, seo_description,
                     is_featured, sort_order
brands               id, name, slug, logo, country_code
products             id, vendor_id, vertical_id, category_id, brand_id,
                     type(enum: simple|variable|quote_only|service_epc),
                     name, slug, sku, hsn_code, short_description, description(rich),
                     unit(kg|ton|unit|set|pack|piece|kW|litre), moq, order_increment,
                     base_price(minor), compare_at_price(minor), currency,
                     tax_class_id, stock_qty, stock_status, lead_time_days,
                     weight_kg, length_cm, width_cm, height_cm,
                     is_active, is_featured, is_bestseller, approval_status(pending|approved|rejected),
                     rejection_reason, rating_cache, reviews_count, views_count,
                     seo_title, seo_description, published_at, timestamps, softDeletes
product_variants     id, product_id, sku, name, attributes json, price(minor), stock_qty,
                     weight_kg, image_id, is_default
product_images       (medialibrary collection: gallery, thumbnail)
product_tier_prices  id, product_id, variant_id, min_qty, max_qty, price(minor), currency
product_attributes   id, vertical_id/category_id, code, label, type(text|number|select|
                     multiselect|bool|date|file), unit, is_filterable, is_required,
                     is_comparable, options json, sort_order
product_attribute_values  product_id, attribute_id, value_text, value_number, value_json
product_certificates product_id, type(same enum as vendor_documents), number, file, expires_at
product_documents    product_id, type(coa|msds|datasheet|spec_sheet|warranty|test_report),
                     file, label
```

**Vertical-specific attribute sets (seeded):**
- **Pharma:** CAS number, molecular formula, purity %, grade (BP/USP/EP/IP), dosage form, strength, pack size, therapeutic category, shelf life, storage conditions, GMP standard, DMF/CEP available, prescription-required flag, **schedule/controlled-substance flag**, batch & expiry (order-level), country registration list.
- **Solar:** wattage (Wp), cell type (Mono PERC/TOPCon/Poly/Thin-film), efficiency %, Voc/Isc/Vmp/Imp, dimensions, no. of cells, frame, connector type, inverter phase/kW/MPPT, battery chemistry/Ah/voltage/cycles/DoD, IP rating, product & performance warranty years, IEC/BIS/MNRE/ALMM listing, container-load qty (pcs/40ft).
- **Main:** generic (material, color, size, power, voltage, packaging, certifications).

### 4.4 Cart, RFQ, Orders
```
carts                id, user_id?, session_id, currency, coupon_code, meta, expires_at
cart_items           cart_id, product_id, variant_id, vendor_id, qty, unit_price(minor),
                     unit, snapshot json
wishlists            user_id, product_id
recently_viewed      user_id/session_id, product_id, viewed_at

rfqs                 id, reference(RFQ-2026-000123), buyer_id, status(draft|open|quoted|
                     accepted|rejected|expired|converted), target_type(product|category|open),
                     product_id?, category_id?, vertical_id, title, description,
                     qty, unit, target_price(minor), currency, destination_country,
                     incoterm(EXW|FOB|CIF|DDP…), delivery_by, attachments, expires_at
rfq_vendors          rfq_id, vendor_id, invited_at, viewed_at, status
quotes               id, rfq_id, vendor_id, reference, status(sent|revised|accepted|
                     rejected|expired), currency, subtotal, shipping, tax, total,
                     incoterm, lead_time_days, validity_until, payment_terms, notes
quote_items          quote_id, description, product_id?, qty, unit, unit_price, total
quote_messages       quote_id, sender_id, body, attachments, created_at   # negotiation thread

orders               id, reference(VX-2026-000456), buyer_id, source(cart|quote),
                     quote_id?, status(pending|confirmed|processing|partially_shipped|
                     shipped|delivered|completed|cancelled|refunded),
                     payment_status(unpaid|authorized|paid|partially_refunded|refunded|escrow_held|released),
                     currency, fx_rate, subtotal, discount, shipping_total, tax_total,
                     grand_total, commission_total, billing_address json, shipping_address json,
                     incoterm, notes, placed_at, timestamps
sub_orders           id, order_id, vendor_id, reference, status, subtotal, shipping, tax,
                     total, commission_amount, vendor_payout_amount, payout_status,
                     escrow_released_at            # har vendor ka apna sub-order
order_items          sub_order_id, product_id, variant_id, name_snapshot, sku, qty, unit,
                     unit_price, tax_rate, tax_amount, total, batch_no?, expiry_date?
order_status_history sub_order_id, from, to, actor_id, note, created_at
```

### 4.5 Payments, Payouts, Shipping, Docs
```
payments             id, order_id, gateway(razorpay|stripe|bank_transfer), gateway_payment_id,
                     gateway_order_id, amount(minor), currency, status(created|authorized|
                     captured|failed|refunded), method, raw_response json, paid_at
refunds              payment_id, sub_order_id?, amount, reason, gateway_refund_id, status
payouts              vendor_id, period_start, period_end, amount, currency, status(pending|
                     processing|paid|failed), gateway_transfer_id, processed_at, sub_order_ids json
ledger_entries       type(sale|commission|refund|payout|adjustment|fee), vendor_id?, order_id?,
                     debit, credit, currency, balance_after, reference, created_at
                     # double-entry-ish audit; monthly reconciliation ka base
shipments            sub_order_id, carrier, service, tracking_no, tracking_url,
                     status(pending|picked|in_transit|customs|out_for_delivery|delivered|exception),
                     shipped_at, delivered_at, weight, packages, incoterm, port_of_loading,
                     port_of_discharge, container_no, bl_awb_no
shipment_events      shipment_id, status, location, description, happened_at
shipping_zones / shipping_rates / vendor_shipping_profiles
export_documents     order_id/sub_order_id, type(proforma_invoice|commercial_invoice|
                     packing_list|coo|bill_of_lading|coa|insurance|customs_declaration),
                     number, file_path, issued_at, issued_by
tax_classes / tax_rates    (GST India, VAT/IGST export-zero-rated logic)
currencies           code, symbol, rate_to_usd, is_active, updated_at
coupons              code, type(percent|fixed|free_shipping), value, scope(platform|vendor),
                     vendor_id?, min_order, max_discount, usage_limit, per_user_limit,
                     starts_at, ends_at, applies_to json
flash_sales / flash_sale_items    # homepage countdown deal
```

### 4.6 Engagement & CMS
```
reviews              product_id, vendor_id, order_item_id, user_id, rating, title, body,
                     images, is_verified_purchase, status(pending|approved|rejected), reply, replied_at
vendor_reviews       vendor_id, user_id, order_id, rating(communication/quality/shipping), body
questions / answers  product Q&A
messages/threads     buyer ↔ vendor inquiry (RFQ ke bahar bhi)
notifications        (Laravel native) + notification_preferences
newsletter_subscribers  email, source, confirmed_at, unsubscribed_at
pages                slug, title, body, seo (About, Careers, Buyer Guide, Vendor Guide,
                     Shipping Info, Returns, Privacy, Terms, Cookies)
posts / post_categories   blog (SEO ke liye — "export trends" content)
testimonials         name, designation, company, country, avatar, rating, body, is_featured
faqs                 category, question, answer, sort_order
banners              placement(hero|category|sidebar), image, link, starts_at, ends_at
settings             key/value (site config, commission, contact, socials)
support_tickets      user_id, order_id?, subject, priority, status, messages
```

**Migration order:** identity → verticals/categories/brands → vendors+docs → products+attributes → cart → rfq/quotes → orders → payments/payouts/ledger → shipping → reviews/CMS.

**Acceptance:** `migrate:fresh --seed` se realistic demo data (3 verticals, ~25 categories, 8 vendors, 120 products with vertical-specific attributes, 20 orders, 5 RFQs) ban jaaye — homepage design ke exact numbers ke saath match karke.

---

## 5. Phase 3 — Auth, Roles & Vendor Onboarding

1. Auth scaffolding **manually** (Blade + Alpine, brand design me): login, register (buyer/vendor toggle), forgot/reset password, email verification, 2FA optional for vendors/admin.
2. `spatie/laravel-permission` roles + permissions matrix; Gates + Policies (`ProductPolicy`, `SubOrderPolicy`, `VendorPolicy` — vendor sirf apna data dekhe).
3. **Vendor onboarding wizard** (multi-step, Alpine stepper):
   Step 1 Account → Step 2 Company (GST/PAN/IEC/CIN) → Step 3 Verticals + categories → Step 4 Documents upload (drug license/WHO-GMP/BIS/ALMM as per vertical) → Step 5 Bank/payout → Step 6 Review & submit.
4. Admin approval queue (Filament): approve / reject with reason / request docs; email + in-app notification; `vendor.approved` event → welcome kit.
5. Global scope: sirf `approved` vendors ke `approved` products storefront pe.
6. Impersonation (admin → vendor) support ke liye, activity log ke saath.

**Acceptance:** naya vendor register → pending → admin approve → vendor panel me login → product add kar sake.

---

## 6. Phase 4 — Storefront (Blade + Alpine)

### 6.1 Route map (`routes/web.php`)
```
/                                       home
/verticals/{vertical:slug}              vertical landing (Pharma / Solar / Main Store)
/c/{category:slug}                      category PLP (nested)
/search?q=&vertical=&filters…           search results
/p/{product:slug}                       PDP
/vendors                                vendor directory (filters: vertical, country, certs, rating)
/vendors/{vendor:slug}                  vendor store (banner, products, about, certs, reviews, policies)
/deals                                  flash sale / hot deals
/new-arrivals
/cart
/checkout  /checkout/payment  /checkout/confirmation/{order}
/rfq/create  /rfq/{rfq}                 RFQ submit + thread
/quotes/{quote}                          quote view / accept
/track-order                             public tracking (reference + email)
/account/*                               dashboard, orders, order detail, rfqs, quotes,
                                         addresses, wishlist, reviews, downloads(docs), settings
/become-a-vendor                         landing + wizard entry
/pages/{slug}  /blog  /blog/{post}  /help  /contact
/sitemap.xml  /robots.txt
```
**JSON endpoints (Alpine ke liye, `routes/api.php` ya `/x/*` web routes with CSRF):**
`POST /x/cart/items`, `PATCH /x/cart/items/{id}`, `DELETE …`, `GET /x/cart`,
`POST /x/wishlist/toggle`, `GET /x/search/suggest`, `GET /x/products/filter` (facets + paginated HTML partial),
`POST /x/newsletter`, `GET /x/currency/{code}`, `POST /x/rfq/quick`.

> Pattern: filter/pagination ke liye server **rendered Blade partial** return karega (`view('partials.product-grid')`) — Alpine usse `innerHTML` me swap karega + `history.pushState`. Isse Livewire ke bina bhi SPA-ish feel aur SEO safe rahega.

### 6.2 Homepage — design se 1:1 mapping
| Design section | Blade component | Data |
|---|---|---|
| Top bar | `nav.topbar` | `settings` (phone, email) |
| Header + search + cart badge | `nav.header` | Alpine `$store.cart.count` |
| Category nav + Flash Sale | `nav.category-nav` | verticals + featured categories mega-menu |
| Hero (stats 50K+/2,500+/150+) | `home.hero` | live counts (cached 1h) |
| Trust badges strip | `home.trust-strip` | static/settings |
| Browse by Category (3 cards) | `home.category-trio` | verticals + counts |
| Pharma section (4 products) | `home.vertical-showcase` | featured pharma products |
| Solar section (4 products) | `home.vertical-showcase` | featured solar products |
| Flash Deal + countdown | `home.deal-banner` | active `flash_sale` |
| Top Verified Vendors (4) | `home.vendor-strip` | top-rated approved vendors |
| Why VEXPORTER + analytics card | `home.why-us` + `home.analytics-card` | live GMV/orders/buyers/countries (cached) |
| Testimonials (3) | `home.testimonials` | `testimonials` table |
| Vendor CTA banner | `home.vendor-cta` | settings (commission %) |
| Newsletter | `home.newsletter` | POST `/x/newsletter` |
| Footer (5 col) | `footer.main` | categories, pages, socials |

### 6.3 PLP (category / vertical / search)
- Sidebar facets: price range (noUiSlider), vendor, country, certifications, brand, rating, MOQ, lead time, in-stock, **vertical-specific attributes** (wattage, cell type, grade, dosage form, purity…).
- Sort: relevance, price ↑↓, newest, rating, bestselling, MOQ.
- Grid/list toggle, 24/48/96 per page, mobile filter drawer (Alpine).
- Meilisearch facet distribution se counts.

### 6.4 PDP
Gallery (PhotoSwipe) · title/SKU/HSN · rating · price + **tier price table** · MOQ-aware qty stepper · unit selector · Add to Cart / **Request Quote** (product `type=quote_only` ya qty > threshold pe RFQ CTA) · vendor mini-card (certs, response time, View Store, Chat) · shipping estimator (destination country → lead time + freight) · tabs: Description, Specifications (attribute table), Certifications, Documents (COA/MSDS/datasheet — login-gated), Shipping & Payment, Reviews, Q&A · related + "same vendor" + recently viewed · JSON-LD `Product` + `Offer` + `AggregateRating`.
- **Pharma guard:** prescription/controlled items pe price hide + "Verified buyers only — license required" gate; buyer license verify hone tak inquiry-only.

### 6.5 Cart & Checkout
- Cart **vendor-wise grouped** (har vendor ka subtotal, shipping, MOQ warning), coupon, currency switch, save-for-later.
- Checkout steps (Alpine stepper, single page): Address → Shipping method per vendor → Incoterm → Review → Payment.
- Guest checkout allowed? → **Nahi** (B2B; account + KYC zaroori). Register-inline.
- Order split: ek `order` → N `sub_orders` (per vendor) auto.
- Payment step pe gateway auto-select: India/INR → Razorpay, baaki → Stripe (user override allowed). Bank transfer (T/T) option with proforma invoice — B2B me common.

### 6.6 RFQ flow
Buyer RFQ submit (product-specific ya open) → matching vendors ko notify (vertical + category match) → vendor quote bhejta hai (line items, incoterm, lead time, validity) → buyer compare screen (side-by-side quotes) → negotiate thread → accept → **quote → order convert** (payment ya T/T terms).

**Acceptance:** demo data pe pura buyer journey — browse → PDP → cart → checkout (test mode) → order confirmation → tracking; aur alag RFQ journey → quote → accept → order.

---

## 7. Phase 5 — Filament Panels

### 7.1 Admin panel `/admin`
- **Dashboard widgets:** GMV chart, orders/day, new vendors pending, RFQs open, top verticals, low-stock, payout due, revenue (commission) — homepage ke "Trade Analytics" card se consistent.
- **Resources:** Users, Buyers (KYC), Vendors (+ approval action, documents relation manager, commission override), Verticals, Categories (tree), Brands, Attributes + options, Products (approve/reject, bulk actions), Orders + SubOrders (status, refund, notes), RFQs, Quotes, Payments, Refunds, Payouts (generate batch, mark paid), Ledger (read-only), Shipments, Coupons, Flash Sales, Reviews (moderate), Q&A, Testimonials, Banners, Pages, Blog, FAQs, Newsletter, Support tickets, Settings (spatie-settings style), Activity log.
- **Policies:** super-admin + staff roles (granular permissions via spatie).

### 7.2 Vendor panel `/vendor`
- Tenancy: Filament multi-tenancy `vendors` model pe (user multiple vendors ka staff ho sakta hai).
- **Dashboard:** sales, pending orders, low stock, payout balance, RFQ invites, product approval status.
- **Resources:** Products (create/edit + variants + tier prices + attributes + certificates + documents; **submit for approval** flow), Bulk import/export (Excel template per vertical), Orders (apne sub-orders only; accept, pack, ship + tracking, upload export docs), RFQ invites → Quote builder, Shipments, Payouts & ledger, Coupons (vendor-scoped), Reviews (reply), Store profile (banner, about, policies, certs), Staff users, Settings.
- **Guardrails:** har query `vendor_id` scoped; policies + tests se enforce.

**Acceptance:** vendor apne alawa kisi aur ka record kabhi na dekh paaye (dedicated Pest test suite).

---

## 8. Phase 6 — Payments, Commission & Payouts

**Abstraction**
```php
App\Payments\Contracts\PaymentGateway   // createIntent, capture, refund, verifyWebhook, transfer
App\Payments\Gateways\RazorpayGateway
App\Payments\Gateways\StripeGateway
App\Payments\Gateways\BankTransferGateway   // T/T with proforma + manual admin confirm
App\Payments\PaymentManager                 // driver resolve: currency/country/setting
```
- **Razorpay:** Orders API + Checkout, webhooks (`payment.captured`, `payment.failed`, `refund.processed`), Route/transfers se vendor split (linked accounts) — Phase 6b.
- **Stripe:** PaymentIntents + Connect (Express) accounts vendors ke liye, `transfer_data`/separate transfers, webhooks (`payment_intent.succeeded`, `charge.refunded`, `account.updated`).
- **Escrow behaviour:** funds platform pe hold → delivery confirm / auto-release after X days → payout. `orders.payment_status = escrow_held → released`.
- **Commission:** platform default 5% (settings), vendor-level override, category-level override; calculate at order placement, freeze in `sub_orders.commission_amount`, ledger entry.
- **Payout cycle:** weekly batch job → eligible sub-orders (delivered + release window passed, no open dispute) → `payouts` row → gateway transfer ya manual bank file (CSV) → mark paid → vendor notification + PDF statement.
- **Idempotency:** webhook table (`gateway_event_id` unique) + queued handler; signature verification mandatory.
- **Multi-currency:** daily FX job (exchangerate API), `orders.fx_rate` freeze, display conversion only (charge in gateway-supported currency).

**Acceptance:** test-mode end-to-end — payment success, failure, refund (full+partial), webhook replay safe, payout batch generate + ledger balanced.

---

## 9. Phase 7 — Logistics, Documents & Compliance

1. **Shipping:** vendor shipping profiles (zone × weight/volume rates), incoterms per order, freight estimator on PDP/checkout, courier integration hooks (Shiprocket/DHL/FedEx API — interface ab, driver baad me), manual tracking entry fallback.
2. **Tracking:** public `/track-order`, buyer account timeline, shipment events, email/WhatsApp notifications.
3. **Export documents:** auto-generate PDF — Proforma Invoice, Commercial Invoice, Packing List, (Certificate of Origin, BL/AWB upload). Numbering series per FY. Vendor + admin dono generate/upload kar saken; buyer downloads.
4. **Compliance:**
   - Pharma: drug license capture, **prescription/controlled item gating**, batch + expiry on order items, COA attach mandatory before ship, country-registration check warning, "not for resale without license" disclaimer, WHO-GMP/FDA cert expiry alerts.
   - Solar: BIS/ALMM/IEC cert validity, warranty terms per product.
   - General: GST invoice (India), export = zero-rated with LUT flag, IEC on buyer/vendor, DGFT/APEDA doc checklist per design's "Export Documentation" promise.
   - Legal pages: Terms, Privacy, Cookies, Refund/Return, Vendor Agreement, Prohibited Items (esp. pharma), Dispute policy.
5. **Disputes/RMA:** buyer raise dispute → escrow hold → admin arbitration → refund/release.

---

## 10. Phase 8 — Search, Performance, SEO

- **Meilisearch:** `products` index (name, sku, description, vendor, category, attributes, certs), synonyms (API↔active pharmaceutical ingredient, PV↔solar panel), typo tolerance, ranking rules (approved+in-stock boost), facets. `vendors` index alag.
- **Caching:** homepage sections (1h tags), category tree, facet counts, currency rates; Redis tags-based bust on model events.
- **Queues:** image conversions, emails, search indexing, webhook processing, payout batches, FX sync, sitemap. Horizon + `supervisor`.
- **Images:** medialibrary conversions (thumb 300, card 600, zoom 1600) + WebP + lazy loading + `loading="lazy"` + LQIP.
- **SEO:** per-page meta, canonical, JSON-LD (Organization, BreadcrumbList, Product, Offer, AggregateRating, FAQPage), sitemap index (products/categories/vendors/blog), hreflang (future), OG/Twitter cards, clean slugs, 301 map.
- **Perf targets:** LCP < 2.5s, CLS < 0.1, TTFB < 300ms cached; no Tailwind CDN (built CSS), no FA CDN (subset), fonts self-hosted with `font-display: swap`.
- **A11y:** focus states, aria on drawers/modals, contrast check (brand red on white ok, red-on-dark check), keyboard nav for mega-menu.

---

## 11. Phase 9 — Testing, Quality, Security

**Pest suites**
- Unit: pricing (tier + coupon + tax), commission calc, FX conversion, MOQ validation, ledger balancing.
- Feature: auth, vendor onboarding+approval, product approval, cart (multi-vendor split), checkout, order lifecycle, RFQ→quote→order, payments (mocked gateways), refunds, payout batch, review moderation.
- **Authorization suite:** vendor isolation (cross-vendor 403), buyer cannot see others' orders, admin-only actions.
- Browser (Pest v4 browser testing / Dusk optional): homepage render, add-to-cart, checkout happy path, filter drawer.
- Static: Larastan level 6, Pint (Laravel preset) on pre-commit.

**Security checklist**
- Rate limiting (login, RFQ, newsletter, search suggest), CSRF everywhere, signed URLs for document downloads, private disk for KYC/docs (never public), encrypted casts for bank/payout details, file upload validation (mime + size + virus scan hook), 2FA for admin/vendor, session security, `X-Frame-Options`/CSP headers, activity log on sensitive actions, GDPR-ish data export/delete for buyers, webhook signature verify, no secrets in repo.

---

## 12. Phase 10 — Deployment & Ops

- Server: Ubuntu + Nginx + PHP-FPM 8.3 + MySQL 8 + Redis + Meilisearch + Supervisor (Horizon) — Laravel Forge recommended.
- Zero-downtime deploy (Envoyer/Forge), `.env` per env, `php artisan optimize`, `queue:restart`, `migrate --force`.
- Storage: S3/R2 for media + private bucket for KYC/docs; CDN (Cloudflare) for public assets.
- Backups: DB nightly + weekly full (spatie/laravel-backup), media sync, restore drill.
- Monitoring: Sentry (errors), Horizon, uptime, log channel to Papertrail/Log viewer, `pulse` optional.
- Staging environment + seeded demo data; feature flags for gateway toggles.

---

## 13. Directory Structure (target)

```
app/
  Models/            User, Vendor, Product, Category, Vertical, Order, SubOrder, Rfq, Quote, …
  Http/Controllers/
    Storefront/      HomeController, CategoryController, ProductController, VendorController,
                     SearchController, CartController, CheckoutController, RfqController,
                     QuoteController, AccountController, TrackingController, PageController
    Ajax/            CartAjaxController, WishlistController, SuggestController, FilterController
    Webhooks/        RazorpayWebhookController, StripeWebhookController
  Actions/           Cart\AddItem, Checkout\PlaceOrder, Orders\SplitByVendor,
                     Payments\CapturePayment, Payouts\GenerateBatch, Rfq\ConvertQuoteToOrder
  Services/          PricingService, ShippingEstimator, CommissionCalculator, FxService,
                     DocumentGenerator, SearchService
  Payments/          Contracts, Gateways, PaymentManager, DTOs
  Filament/
    Admin/Resources|Widgets|Pages
    Vendor/Resources|Widgets|Pages
  Policies/  Events/  Listeners/  Jobs/  Notifications/  Observers/  Enums/  Support/
resources/
  views/
    layouts/  components/  storefront/  account/  emails/  pdf/  partials/
  css/app.css   js/app.js   js/alpine/*.js
routes/  web.php  ajax.php  webhooks.php  console.php
database/  migrations/  factories/  seeders/
tests/  Unit/  Feature/  Browser/
```

---

## 14. Milestones & Effort (rough)

| # | Phase | Deliverable | Est. |
|---|---|---|---|
| 0 | Setup | env, DB, tooling | 0.5 day |
| 1 | Design system | tokens, logo, ~45 Blade components, Alpine core, homepage live | 4–5 days |
| 2 | Data layer | migrations, models, factories, seeders (demo data) | 4 days |
| 3 | Auth + vendor onboarding | register/login, wizard, approval | 3 days |
| 4 | Storefront | PLP, PDP, vendor store, cart, checkout, RFQ, account | 8–10 days |
| 5 | Filament panels | admin + vendor (tenancy, resources, widgets) | 6–7 days |
| 6 | Payments & payouts | Razorpay + Stripe + escrow + commission + ledger | 5 days |
| 7 | Logistics & docs | shipping, tracking, PDFs, compliance | 4 days |
| 8 | Search/perf/SEO | Meilisearch, caching, schema, sitemap | 3 days |
| 9 | Testing & hardening | Pest suites, Larastan, security pass | 4 days |
| 10 | Deploy | Forge/staging/prod, backups, monitoring | 2 days |

**Total ≈ 8–9 weeks** single dev, full-time. MVP cut (Phase 0–5 + basic Razorpay) ≈ **4–5 weeks**.

---

## 15. Risks & Open Items

1. ~~**Filament 4 × Laravel 13 compatibility**~~ — ✅ **resolved**: Filament v4.12.4 Laravel 13 pe clean install hota hai (Livewire 3.8.3 ke saath). Note: Windows pe `composer.bat` cmd se chalta hai jahan `^` escape character hai — version constraints `"4.*"` style me likho, `^4` silently `4` ban jaata hai.
2. **Design sirf homepage ka hai** — baaki pages hum design language extend karke banayenge; agar aapke paas aur mockups/Figma hai to Phase 1 se pehle do.
3. **Logo asset raw file nahi mila** (design me CSS-drawn "V" hai) — proper SVG/PNG logo file ho to bhejna, warna hum vector redraw karenge.
4. **Pharma regulatory scope** — kaunse countries target hain? Controlled/prescription items list aur license verification policy business side se confirm chahiye.
5. **Razorpay Route / Stripe Connect** approval business KYC pe depend karta hai — parallel me apply karna, warna manual payout (bank CSV) fallback pehle chalu.
6. **Meilisearch hosting** — self-host vs Meilisearch Cloud decide karna (dev me local Docker/binary).
7. **Vendor commission model** — flat 5% (design me likha hai) vs vertical-wise slabs? Abhi flat + override rakh rahe hain.
8. **WhatsApp/SMS notifications** (B2B India me high value) — scope me daalein? Abhi hook rakha hai, integration optional.

---

## 16. Immediate Next Steps (Phase 0+1 kickoff)

1. Filament 4 + Laravel 13 compatibility verify → packages install.
2. MySQL DB banao, `.env` update, migrate.
3. `resources/css/app.css` me brand tokens + custom utilities port.
4. Fonts (Inter/Poppins) + Font Awesome self-host, Tailwind CDN dependency hatao.
5. `layouts/storefront` + header/topbar/category-nav/footer components banao.
6. Homepage ko static data ke saath 1:1 render karo (design se pixel-close), phir Phase 2 me data-driven.
