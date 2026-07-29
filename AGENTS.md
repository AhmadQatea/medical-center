# Doctors Bookings — Agent Project Guide

This file is the source of truth for AI agents working on this repository.
Keep Laravel Boost guidelines (below) intact. Prefer this project section for product, architecture, and implementation status.

---

## Product overview

**Name (concept):** Single-doctor dental appointment management system (not SaaS, not multi-tenant).

**Clinic (current config / DB defaults):**
- Clinic: `العيادة السنية التخصصية` (`config/clinic.php`)
- Doctor display name: `العيادة السنية التخصصية` (no personal doctor branding)
- Specialty: طبيب أسنان / Dentist
- Locale UI: Arabic RTL (Cairo font)
- Theme: Deep Burgundy `#6B1E2A`, Gold `#C9A84C`, warm white background
- Timezone: `Asia/Damascus` (`APP_TIMEZONE`, `CLINIC_TIMEZONE`)

**Actors:**
1. **Doctor** — one authenticated account (Breeze login). Manages dashboard, bookings, patients, schedule, timeline, settings, profile, appointment types.
2. **Patient** — guest only. Books via public `/book`. No patient accounts.

**Login (local seeder defaults):**
- Email: `clinic@example.com` (override with `DOCTOR_EMAIL` / `config('clinic.seed_doctor')`)
- Password: `admin123123` locally (override with `DOCTOR_PASSWORD`; **required** outside `local`/`testing`)
- Login rate limit: 5 attempts (`LoginRequest` + route `throttle:5,1`)

**Public registration:** disabled. Single seeded doctor only.

**Remote:** [AhmadQatea/d.mostafaBakro](https://github.com/AhmadQatea/d.mostafaBakro)

---

## Stack

| Layer | Choice |
|---|---|
| PHP | 8.2 |
| Framework | Laravel 12 |
| Auth | Laravel Breeze (Blade) |
| Frontend | Blade + Tailwind CSS v4 + Alpine.js |
| Tests | Pest 3 |
| Format | Laravel Pint |
| Bundler | Vite |

Related configs: `config/clinic.php`, `config/theme.php`, `resources/css/themes/tokens.css`.

---

## Features (product surface)

| Feature | Route area | Status |
|---|---|---|
| Public booking | `/book`, `/book/success` | **Implemented** — Pending in DB; week→day→time flow; success gated by session |
| Instant booking | `/doctor/bookings/instant` | **Implemented** — same picker; creates Confirmed/Pending; redirects to details |
| Bookings list / details | `/doctor/bookings` | **Implemented** — filter, status actions, WhatsApp confirm button |
| Doctor dashboard | `/doctor/dashboard` | **Implemented** — real stats + pending/confirmed/today/upcoming |
| Timeline | `/doctor/timeline` | **Implemented** — today's slots + appointments |
| Schedule management | `/doctor/schedule` | **Implemented** — hours, lunch, holidays, slot computation |
| Appointment types | `/doctor/appointment-types` | **Implemented** — CRUD, toggle, reorder (DB table, not enum) |
| Patients | `/doctor/patients` | **Index only** — list/search; full CRUD deferred |
| Clinic settings | `/doctor/settings` | **Implemented** |
| Doctor profile UI | `/doctor/profile` | Wired to Breeze `profile.update` / `password.update` |
| Auth (login/logout/password) | Breeze `auth.php` | Working + route throttles |

---

## Architecture conventions (this app)

### Layers
1. **Routes** — thin; named routes; split by responsibility.
2. **Controllers** — thin; constructor DI of Services; **no business logic**.
3. **Form Requests** — validation (+ ownership checks where needed).
4. **Actions** — single write use-cases with DB transactions when needed.
5. **Services** — domain orchestration (reads/writes shared across controllers).
6. **Models** — Eloquent only: `$fillable`, `casts()`, relationships, route binding. **No business logic.**
7. **Enums** — string-backed domain values with `label()`, `color()`, helpers (`canConfirm()`, `occupiesSlot()`, etc.).

### Ownership
- Single doctor → every domain row has `user_id` pointing at that doctor.
- **Do not put `user_id` in `$fillable`.** Set via relations.
- **Scoped route binding:** `BelongsToDoctorRouteBinding` on `Appointment`, `AppointmentType`, `Holiday`, `Patient` — resolves only rows owned by `auth()->id()`.
- **No Policies** at this stage. Do **not** add Gate/policy/`authorize()` unless the user asks.

### Controllers stay thin
```text
Controller → FormRequest (validate) → Action and/or Service → Model
```

### Transactions & concurrency
- Prefer **one** transaction boundary (usually in Action or `BookingService`).
- Booking create/update: `DB::transaction` + `lockForUpdate()` + unique `slot_guard_key`.
- Active slot conflict = same date + start time + status **pending|confirmed**.

---

## Routes

Registered in `bootstrap/app.php` (`web` + `then` groups):

| File | Prefix / role | Middleware |
|---|---|---|
| `routes/web.php` | `/` → `/book`, `/dashboard` → doctor, Breeze `/profile` | `auth` where needed |
| `routes/booking.php` | `/book` · `booking.*` | public; `POST` throttled `10,1` |
| `routes/doctor.php` | `/doctor` · `doctor.*` | `auth` + `throttle:120,1` |
| `routes/auth.php` | login, password, verify, logout | `guest` / `auth`; login/reset throttled `5,1` |

Important names:
- `booking.index`, `booking.store`, `booking.success`
- `doctor.dashboard`, `doctor.bookings.*`, `doctor.bookings.instant`, `doctor.bookings.confirm`, `doctor.bookings.status`
- `doctor.timeline.index`, `doctor.schedule.*`, `doctor.patients.index`
- `doctor.appointment-types.*`, `doctor.settings.*`, `doctor.profile.index`
- Resource bookings use parameter `{appointment}` → `Appointment` model.

---

## Controllers

### Doctor (`app/Http/Controllers/Doctor/`)
- `DashboardController` (invokable) — wired
- `BookingController` (resource + `instant` + `confirm` + `updateStatus`) — wired
- `PatientController` — **index only**
- `ScheduleController` (`index` / `update` / holidays) — wired
- `TimelineController` (invokable) — wired
- `AppointmentTypeController` — wired
- `ClinicSettingsController` — wired
- `ProfileController` — **index only** (updates via Breeze `/profile`)

### Public
- `Booking\PublicBookingController` — `index` / `store` / `success`

### Breeze
- `ProfileController` (root) — real profile update/delete
- `Auth\*` — session, password reset, verification

---

## Services (`app/Services/`)

| Service | Status |
|---|---|
| `ClinicSettingsService` | **Implemented** |
| `BookingService` | **Implemented** — create public/instant, update, cancel, list, dashboard stats; slot locking |
| `ScheduleService` | **Implemented** — settings, hours, holidays, `availableSlots`, `bookingWeeks` |
| `PatientService` | **Implemented** — list, findOrCreate (CRUD UI deferred) |
| `TimelineService` | **Implemented** — `forToday` / `forDate` |
| `WhatsAppService` | **Implemented** — patient confirmation deep link + message |

---

## Actions (`app/Actions/`)

| Action | Status |
|---|---|
| `ClinicSettings\UpdateClinicSettingsAction` | **Implemented** |
| `Schedule\UpdateScheduleAction` | **Implemented** |
| `Booking\CreatePublicBookingAction` | **Implemented** |
| `Booking\CreateInstantBookingAction` | **Implemented** |
| `Booking\ConfirmAppointmentAction` | **Implemented** — lock + guards |
| `Booking\UpdateAppointmentStatusAction` | **Implemented** — lock + transitions |
| `Booking\GenerateBookingConfirmationMessage` | **Implemented** |
| `AppointmentType\*` (Create/Update/Delete/Toggle/Reorder) | **Implemented** |

Pattern: `app/Actions/{Domain}/{Verb}{Entity}Action.php`.

---

## Database

### Domain tables
Order: `users` → settings/hours/holidays/patients/`appointment_types` → `appointments`.

1. **users** — doctor auth
2. **clinic_settings** — 1:1 brand, specialty, WhatsApp, media paths
3. **schedule_settings** — duration, break, lunch
4. **working_hours** — unique (`user_id`, `weekday`) — 0=Sunday…6=Saturday
5. **holidays** — unique (`user_id`, `date`)
6. **patients** — SoftDeletes; no auth
7. **appointment_types** — doctor-managed visit types (`name`, `color`, `is_active`, `display_order`)
8. **appointments** — `appointment_type_id` FK; `slot_guard_key` nullable unique per `(user_id, slot_guard_key)` for pending|confirmed

### Design decisions
- **No `time_slots` table** — computed at runtime.
- Slot occupancy: only **pending** and **confirmed** (via `AppointmentStatus::occupiesSlot()` + `slot_guard_key`).
- Completed / cancelled / no_show free the slot.
- Soft-delete patients; do not hard-delete booked patients.

### Factories / seeder
- Factories for all domain models.
- `DatabaseSeeder` uses `config('clinic.seed_doctor')` + `ClinicSettingsService::get()` + `AppointmentTypeSeeder`.

---

## Enums (`app/Enums/`)

| Enum | Notes |
|---|---|
| `AppointmentStatus` | `pending`, `confirmed`, `completed`, `cancelled`, `no_show` — transitions + UI helpers |
| `AppointmentSource` | `instant`, `public`, `whatsapp` |
| `BookingStatus` | Shared labels (align with AppointmentStatus values) |
| `WeekDay` | Arabic week order |

**Appointment types are a DB model** (`App\Models\AppointmentType`), not a PHP enum.

---

## Security & production hardening

- Unique DB constraint on active slots (`slot_guard_key`)
- `lockForUpdate()` on booking create/update and status changes
- Validation: past date/time, holiday, closed day, inactive type, phone `+9639xxxxxxxx`, name min 2
- Doctor guards: no confirm cancelled/completed; no cancel/edit completed
- Mass assignment: `password`, `status`, `source`, media paths not in `$fillable` where unsafe
- Auth/password reset/doctor routes rate-limited
- Stub mutation routes removed (patients CRUD, doctor.profile update/destroy)
- Production `.env`: `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` (HTTPS)

---

## UI / frontend

- Layouts: `layouts/doctor` (RTL sidebar + navbar), `layouts/booking`, `layouts/guest`
- Shared design system in `resources/css/app.css` (`ds-*` utilities)
- Public + instant booking: week → day → time → patient → summary (sticky mobile CTA, loading states)
- Components: `ui`, `form`, `layout`, `doctor`, `booking`, `theme`
- UX: large touch targets, flash auto-dismiss, empty states with icons, a11y skip links / `aria-*`
- After CSS/JS changes: `npm run build` or `npm run dev`

---

## Testing

- Pest Feature tests with `RefreshDatabase`
- Key suites: `QaUserFlowsTest`, `BookingHardeningTest`, `SecurityAuditTest`, `BookingWorkflowTest`, `BookingReviewWorkflowTest`, `PublicBookingFlowTest`, `ScheduleManagementTest`, `ClinicSettingsTest`, Breeze auth/profile
- Helper: `bookingWeekdayPayload()` in `tests/Pest.php`
- Run: `php artisan test --compact`
- After PHP edits: `vendor/bin/pint --dirty --format agent`

---

## Work completed (agent history summary)

1. Single-doctor clinic app (not multi-tenant SaaS).
2. Premium Arabic RTL UI for doctor + public booking.
3. DB architecture, models, factories, seeders.
4. Routes + thin controllers + Actions/Services.
5. **Clinic settings**, **schedule**, **appointment types**, **bookings** (public + instant), **timeline**, **dashboard** — implemented end-to-end.
6. Booking review workflow: details page, confirm → WhatsApp button (no auto-redirect).
7. Production hardening: slot uniqueness, locking, validation, IDOR scoped binding, throttles.
8. Security audit fixes + QA flow tests (~114 tests).
9. UX polish: mobile sticky CTAs, loading states, empty states, a11y, medical visual hierarchy.

---

## Explicit non-goals (for now)

- Multi-clinic / Super Admin / subscriptions / tenant themes
- Patient authentication
- Payments / invoices
- Policy/Gates authorization layer
- Full Patients CRUD UI (service methods exist; routes index-only)

---

## Recommended next work (optional)

1. Patients CRUD UI (Form Requests + Actions wired to existing `PatientService`)
2. Optional file uploads for clinic logo/photo (with strict validation)
3. Email verification enforcement if needed
4. Deploy checklist (migrate, seed with `DOCTOR_PASSWORD`, HTTPS session cookies)

When implementing a feature: Form Request → Action → Service methods → thin controller → Blade wiring → Pest tests. Do not dump business logic into controllers or models.

---

## Key paths cheat sheet

```
app/Http/Controllers/Doctor/*
app/Http/Controllers/Booking/PublicBookingController.php
app/Services/*
app/Actions/{Booking,Schedule,ClinicSettings,AppointmentType}/*
app/Models/*
app/Models/Concerns/BelongsToDoctorRouteBinding.php
app/Enums/*
app/Rules/{ValidBookableSlot,ValidBookingDate,ActiveAppointmentType}.php
routes/{web,booking,doctor,auth}.php
config/{clinic,theme}.php
resources/views/{doctor,booking,layouts,components}/*
resources/css/app.css
resources/js/booking-flow.js
database/migrations/*
tests/Feature/*
```

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
