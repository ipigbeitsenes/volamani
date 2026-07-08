# Volamani

**A Nigerian/African digital business ecosystem — Shopify + Fiverr + Upwork + Gumroad in one platform.**

Volamani lets African entrepreneurs sell **digital products**, offer **freelance services**, run **paid consultations**, and sell **physical goods**, with built‑in **wallet, escrow, disputes, KYC, reviews, referrals, subscriptions, invoicing and an AI‑style pricing assistant** — all behind one storefront with secure payments.

---

## Table of contents
1. [Tech stack](#tech-stack)
2. [Architecture](#architecture)
3. [The money model (read this first)](#the-money-model)
4. [User roles & areas](#user-roles--areas)
5. [Core workflows](#core-workflows)
6. [Directory map](#directory-map)
7. [Local setup (Docker)](#local-setup-docker)
8. [Seeded accounts](#seeded-accounts)
9. [Testing & verification](#testing--verification)
10. [Production deployment checklist](#production-deployment-checklist)
11. [Project status](#project-status)

---

## Tech stack
- **Laravel 12**, **PHP 8.3**, Blade templates
- **Bootstrap 5.3** + Bootstrap Icons (CDN, no build step), vanilla JS
- **MySQL 8**, **Redis**, **Docker** (nginx + php‑fpm + mysql + redis + mailpit + queue + scheduler)
- `spatie/laravel-permission` (roles/permissions), `spatie/laravel-activitylog` (audit)
- Payments via **Paystack** (Laravel HTTP client) + manual **bank transfer**
- **No** React/Vue/Inertia/Livewire/Tailwind — it is a server‑rendered monolith.

> **Money is stored in kobo (integers).** Never store naira floats. See [the money model](#the-money-model).

---

## Architecture

Strict layered flow — every feature follows it:

```
Controller  ->  Action        ->  Service          ->  Repository  ->  Model
(thin: flash    (one workflow      (orchestrates        (all DB         (relations
 + redirect)     per class)         actions/logic)       queries)        + casts only)
```

- **Controllers** are thin — validate (via Form Requests), call a service, flash + redirect.
- **Actions** encapsulate a single workflow (e.g. `HoldEscrowAction`, `OpenDisputeAction`).
- **Services** orchestrate actions and hold business logic (e.g. `EscrowService`, `CartCheckoutService`).
- **Repositories** own all database queries.
- **Models** hold relationships + casts (+ small helpers); no query logic.
- **Form Requests** validate; **policies / inline `abort_unless`** authorize.
- All financial operations run inside **DB transactions**.

---

## The money model

- **All amounts are integers in kobo** (100 kobo = ₦1). Use the helpers in `app/Helpers/helpers.php`: `money()` (format for display), `to_kobo()`, `from_kobo()`.
- **Wallet** has two balances: `balance` (spendable) and `escrow_balance` (pending vendor earnings, not spendable).
- **Immutable ledger**: every spendable‑balance movement writes a `wallet_ledgers` row. Escrow holds do **not** write a ledger entry (they are tracked in `escrow_transactions`) so wallet reconciliation stays valid.
- **Escrow lifecycle**: payment success → escrow opens (`holding`), vendor `escrow_balance` += earnings. On release → moves escrow → spendable `balance` (ledger credit). On refund → credits the **buyer** wallet and reverses the vendor escrow.
- **Platform commission** is carved out per sale; affiliate/referral commissions are paid from the platform's cut.

---

## User roles & areas

| Role | Area (URL prefix) | Layout | What they do |
|------|-------------------|--------|--------------|
| **Buyer** (default) | `/` + `/dashboard` (`layouts.account`) | Account | Browse, buy, track orders, wallet, disputes/returns, KYC |
| **Vendor** | `/vendor/*` | Dark sidebar | Manage products/services/consultations, orders, payouts, storefront, KYC |
| **Admin** | `/admin/*` | Purple sidebar | Moderate products, approve vendors/KYC, disputes, escrow, etc. |
| **Super‑admin** | `/admin/*` (full) | Purple sidebar | Everything admin **plus** settings, user management, commissions, withdrawals |
| **Support team** | `/support/*` | Teal sidebar | Support tickets (disputes), returns/RMA, KYC verification |
| **Finance team** | `/finance/*` | Green sidebar | Payments, withdrawals/payouts, escrow release/refund, commission settings |

**Admin vs super‑admin:** both reach `/admin`, but `settings`, `users`, `commissions`, and `withdrawals` are reserved for **super‑admin** (enforced by trimmed role permissions + `permission:` route middleware + a `Gate::before` that lets super‑admin pass everything). The sidebar hides what a regular admin can't access.

**Support & finance** are separate staff consoles with their own roles, layouts and dashboards; they reuse the same underlying services as the admin area. After login, each role is auto‑redirected to its own dashboard.

---

## Core workflows

### 1. Accounts & onboarding
- **Register/login** (`/register`, `/login`) → buyer role + wallet created automatically; a unique `username` is auto‑generated.
- **Become a vendor**: `/vendor/onboarding` → creates a `Vendor` (status `pending`) → an admin approves it (`/admin/vendors`) which flips it to `active` and grants the `vendor` role.
- **KYC**: users submit identity docs (`/kyc`) → support/admin reviews (`/support/kyc` or `/admin/kyc`) → verified/rejected. Documents stream from a **private** disk, never public.

### 2. Selling
- **Digital products** — uploaded files; buyer downloads instantly after purchase via signed URLs.
- **Freelance services** — tiered packages (Basic/Standard/Premium), requirements → delivery → accept, with revisions.
- **Consultations** — consultant profile + packages + weekly availability; buyers book a session (`/consultations/book/{consultant:slug}`).
- **Physical products** — `products.kind = physical`, with `product_physical_details`, optional **variants**, stock, and flat per‑vendor shipping.
- **Reverse marketplace** — buyers post **requests**; vendors submit **quotations**; buyer accepts one.

### 3. Buying & checkout
- **Cart** (digital + service packages): session‑backed, multi‑vendor. Checkout splits into one order per vendor and settles them together. **Wallet pay‑all** for multi‑seller; card/bank only for single‑seller carts. Idempotent (a cache‑lock guards against double‑charge).
- **Physical products**: direct "Buy Now" checkout (variant/qty/address + shipping), not the session cart.
- **Payment methods**: Paystack (card), bank transfer (upload proof → admin/finance approves), or wallet balance.
- **Gateway return**: after Paystack, the browser returns to the **public** `/checkout/callback` → verifies by reference → `/checkout/success`. These are intentionally not auth‑gated so the external round‑trip never bounces the user to login. Fulfillment is also driven server‑side by the Paystack **webhook**.

### 4. Escrow, disputes & returns
- On payment success an **escrow** opens and holds the vendor's earnings.
  - **Digital products**: buyer has a **24‑hour window** to open a **support ticket**; otherwise the escrow **auto‑releases after 3 business days** (Nigerian holidays respected via `BusinessDayCalculator`).
  - **Services/consultations**: release on delivery acceptance / completion.
- **Support tickets** are escrow‑backed **disputes**. Opening one **freezes** the escrow. Support/admin resolves: release to vendor, refund to buyer, or split.
- **Returns/RMA** (physical): request → vendor/support approve → buyer ships back → confirm receipt → escrow refund + restock.

### 5. Payouts & money‑out
- Vendors request **withdrawals** from spendable wallet balance (min amount + fee configurable).
- **Finance** (or super‑admin) approves/rejects withdrawals; rejected funds return to the wallet.

### 6. Growth & retention
- **Referrals/affiliates** — share a link; the referrer earns a % of the **platform commission** on referred transactions (ongoing).
- **Subscriptions** — vendor plans that can override commission rates.
- **Follow / social commerce** — follow stores; followers are notified on new product approvals.
- **Reviews & trust** — verified‑purchase reviews, helpful votes, vendor trust score.
- **Pricing assistant** — multi‑step calculator producing saved estimates.

### 7. Platform operations
- **Notifications** — every notify goes through `NotificationService` → database (in‑app bell on every layout) + email, honoring per‑category user preferences.
- **Invoices/quotations/documents**, **client management**, **business matching**, **admin management**, and an **audit + security log** (auth events, account lockout) round out the platform.

---

## Directory map

```
app/
  Actions/        # one workflow per class (Escrow, Disputes, Returns, Payment, ...)
  Console/        # scheduled commands (escrow auto-release, promo expiry)
  Enums/          # backed enums w/ label()/badge()/icon()
  Gateways/       # PaystackGateway (PaymentGatewayInterface)
  Http/
    Controllers/  # Admin, Finance, Support, Vendor, Payment, Cart, Disputes, ...
    Middleware/   # SecurityHeaders, EnsureVendorApproved, EnsureKYCVerified
    Requests/     # Form Request validation
  Models/         # relationships + casts
  Notifications/  # VolamaniNotification base + concrete notifications
  Repositories/   # all DB queries
  Services/       # business logic orchestration
  Support/        # BusinessDayCalculator
config/           # payment.php, business_days.php, ...
database/
  migrations/     # schema
  seeders/        # Roles, Admin, Staff, Settings, categories + Demo* (non-prod)
resources/views/  # Blade: layouts/{app,account,vendor,admin,support,finance}, marketplace/*, ...
routes/           # web, auth, marketplace, vendor, admin, support, finance, webhooks, console
tests/            # Feature + Unit (PHPUnit, sqlite :memory:)
tools/            # bash helper/verification scripts (sweep.sh = the regression sweep)
docker/           # nginx + php-fpm config
```

---

## Local setup (Docker)

> Use **one host consistently** — `http://127.0.0.1:8000` (matches `APP_URL` + the Paystack callback). Mixing `localhost` and `127.0.0.1` gives you separate login sessions.

```bash
cp .env.example .env          # then set APP_KEY, DB, Paystack, mail
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate      # if APP_KEY not set
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

App: **http://127.0.0.1:8000** · Mailpit (local email inbox): **http://localhost:8025**

Useful commands:
```bash
docker compose exec app php artisan test          # run the suite
bash tools/sweep.sh                               # 6-role 5xx regression sweep
docker compose restart app nginx                  # after PHP edits (opcache)
docker compose exec app php artisan view:clear     # after Blade edits
```

---

## Seeded accounts

System accounts (all environments):

| Role | Email | Password |
|------|-------|----------|
| Super‑admin | `superadmin@volamani.com` | `SuperAdmin@123456` |
| Admin | `admin@volamani.com` | `Admin@123456` |
| Support | `support@volamani.com` | `Support@123456` |
| Finance | `finance@volamani.com` | `Finance@123456` |

Demo content (non‑production only): buyers/vendors/consultants such as `chioma@example.com`, `pixel@example.com` — all password `password`. **`@example.com` addresses are fake — emails to them never arrive; use a real address or Mailpit to test email.**

> ⚠️ These passwords are hard‑coded in the seeders for development. **Rotate them before any real deployment.**

---

## Testing & verification
- **PHPUnit suite**: `docker compose exec app php artisan test` (sqlite in‑memory; 36 tests passing).
- **Route sweep**: `bash tools/sweep.sh` hits every parameterless GET route as guest + admin/super‑admin/vendor/buyer/support/finance and flags any 5xx (currently **0×5xx across 107 routes**).
- **CI**: `.github/workflows/ci.yml` runs `composer install` + `php artisan test` on push/PR.
- Production cache build verified: `config:cache`, `route:cache`, `view:cache`, `event:cache` all succeed.

---

## Production deployment checklist

The code is production‑ready; these are the **operational** steps before going live:

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, fresh `APP_KEY`.
- [ ] **Rotate all seeded admin/staff passwords** (demo seeders already skip when `APP_ENV=production`).
- [ ] **Secrets**: move Paystack live keys + SMTP credentials into a secrets manager / server env, never in the repo. (`.env` is git‑ignored; keep it that way.)
- [ ] **Queues**: switch `QUEUE_CONNECTION` from `sync` to `redis`/`database` and run a `queue:work` worker (notifications/emails are already `ShouldQueue`, so this makes user actions instant).
- [ ] **Email deliverability**: use a transactional provider (Postmark/SES/etc.) with valid **SPF/DKIM/DMARC** so mail reaches inboxes instead of spam.
- [ ] **Sessions/cookies**: `SESSION_SECURE_COOKIE=true`, set `SESSION_DOMAIN`, serve over HTTPS (the app force‑upgrades to https in production).
- [ ] **Caching**: run `php artisan config:cache route:cache view:cache event:cache` on deploy.
- [ ] **Scheduler**: ensure `php artisan schedule:run` runs every minute (escrow auto‑release, promo expiry).
- [ ] **Webhook**: register the Paystack webhook URL + secret.
- [ ] **Storage**: `php artisan storage:link`; consider S3 (admin Settings has a Local⇄S3 toggle).
- [ ] **Backups & monitoring**: automated DB backups + an error tracker (e.g. Sentry).

---

## Project status

All 22 core modules (0–21) are complete, plus physical commerce, support & finance consoles, the super‑admin split, returns/RMA, referral payouts, and the buyer‑protection ticket model. The platform runs end‑to‑end on Docker with a clean route sweep and a green test suite.

**Known follow‑ups (not blockers):** broader automated test coverage for the newest flows (physical checkout, returns, role split); the operational items in the checklist above; and remaining buyer‑protection ideas (chargebacks, dispute SLAs, KYC‑tiered limits, a public policy page).
# volamanimultivendor
