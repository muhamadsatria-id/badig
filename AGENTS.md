# AGENTS.md — BADIG

Internal intelligence database for Sat Intelkam (4 units + Super Admin). Laravel 13 / PHP 8.3+, Blade + Alpine + Tailwind v4 + Vite. UI copy, comments and docs are **Indonesian**; keep new user-facing strings in Indonesian.

Deep reference lives in `docs.md` (full system map, route table, schema) and `report.md` (QA + known bugs). Read those before large changes.

## Commands

```bash
composer setup                 # full install: deps + .env + key + migrate + npm install + build
composer dev                   # serve + queue:listen + pail + vite, concurrently
composer test                  # config:clear then php artisan test
php artisan test --filter=SecurityHeadersTest   # single test / file
vendor/bin/pint                # formatter (laravel/pint; no composer script exists)
npm run dev / npm run build    # Vite HMR / production assets -> public/build
php artisan db:seed            # AdminSeeder (idempotent; never overwrites existing passwords)
php artisan security:migrate-private-files [--dry-run]   # move legacy public files to private disk
```

Tests use SQLite `:memory:` with array cache/session (see `phpunit.xml`). Only 4 test files exist (`tests/Unit`, `tests/Feature`) — there is no broad suite.

## Architecture facts that are easy to get wrong

- **No `routes/api.php`.** Every route is in `routes/web.php`. Health check is `GET /up`.
- **Custom session auth** in `AuthController` — no Breeze/Fortify/Jetstream. `routes/console.php` only has `inspire`; there are no scheduled tasks and no dispatched jobs (`QUEUE_CONNECTION=database` is wired but unused).
- **5 fixed integer roles** (`users.role_id` 1–5), no permission package/policies. Invariant enforced at login and by `role.unit.valid` middleware: roles 1–4 require `unit_id === role_id`; Super Admin (5) requires `unit_id === null`.
- **Unit isolation is 3 layers** and is the core of the app:
  1. `unit.isolation` middleware validates the `{unit}` param (`unit1`..`unit4`) against user access.
  2. Controller `ChecksUnitAccess`.
  3. `App\Models\Concerns\BelongsToUnit` global scope forces `WHERE unit_id = ...` on all unit queries (Super Admin with `unit_id = null` sees all; null unit → `WHERE 1=0`).
  Adding a "unit" is **not** just inserting a `units` row — it needs a role, a table/model, and the `$modules` map in `IntelUnitController` (currently duplicated in `UnitDocumentController`).
- **All uploaded files live on the private `local` disk** (`storage/app/private/badig/...`). Serve them only through the authorized preview/download routes; never build a public URL. `SecureMediaStorage`'s public-disk path is legacy.
- **`audit_logs` is immutable**: MySQL triggers `trg_audit_logs_no_update`/`trg_audit_logs_no_delete` block UPDATE/DELETE. Log via `AuditLog::record($action, $description, $user)`. The triggers are created **only on MySQL** (skipped on SQLite tests).
- **Livewire + Flux are installed but effectively unused** — no `app/Livewire`, no `<livewire:…>`. Flux provides icons/CSS/tags only. Do not assume Livewire patterns.
- **Tailwind v4** via `@tailwindcss/vite`; design tokens/semantic colors are CSS vars in `resources/css/app.css` (mapped in `tailwind.config.js`). Dark theme is the default (`.dark`, `localStorage('theme')`, pre-paint script).
- **Shared-hosting shim**: root `index.php` + `.htaccess` rewrite to `public/` and pin PHP 8.4 (cPanel). Deploy target is cPanel; DB backup is a pure-PHP SQL dump (no `mysqldump`).
- **`backups/` at repo root = code snapshots**, not DB backups. App-generated DB backups go to `storage/app/private/backups/`.
- Rate limiter names (defined in `AppServiceProvider`): `login`, `sensitive-write`, `downloads` (unduhan berkas nyata, 60/menit), `media` (foto/pratinjau inline, 300/menit), `heavy-export`, `imports`, `backup`. Use them on new write/export routes. Jangan pakai `downloads` untuk media inline — gambar dimuat berkali-kali per halaman sehingga kuota unduhan cepat habis (429).

## Known issues / landmines (see `report.md` §7)

- Legacy sensitive files still exist under `public/storage/badig/...` and are directly downloadable without auth. Do not add to that tree; treat `public/storage` as unsafe for sensitive data.
- `.envv` (root) contains **production credentials** and is **not** matched by `.gitignore` (`.env.*` doesn't match `.envv`). Never commit it.
- `Schema::hasTable`/`hasColumn` is called per-request in several hot paths (`layouts/app.blade.php`, `IntelUnitController`, `AuditLog` on every insert) — a known latency source.
- Dead/unrouted code exists (`ArchiveController`, `GlobalSearchController`, `SecureDownloadController`, `FileManagerController`, `MasterFileManagerController`, `AuditLogController`, `SuperAdminBackupController` with an `exec(mysqldump …)`). Don't wire these up.
- `.env.example` still references the non-existent `BadigAccountSeeder` / `INITIAL_SUPER_ADMIN_*`; the real seeder is `AdminSeeder` (env: `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `UNIT1_EMAIL`..`UNIT4_EMAIL`, `UNIT_PASSWORD`).
