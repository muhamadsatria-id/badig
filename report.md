# BADIG — Laporan QA, Review & Pengujian Sistem

> **Tanggal:** 6 Oktober 2026 · **App URL:** http://127.0.0.1:8000 (php artisan serve, PHP 8.4.25, Windows)
> **Metode:** QA lintas lapisan — (1) uji runtime browser via Chrome DevTools MCP (DOM, console, network, performance trace, Lighthouse), (2) audit backend (routes, middleware, log, rate-limit), (3) review kode statis multi-sumbu, (4) suite test otomatis.
> **Skills yang dipakai:** `browser-testing-with-devtools`, `performance-optimization`, `code-review-and-quality`, `using-agent-skills`.

---

## 1. Ringkasan Eksekutif

Sistem **BADIG** secara keseluruhan **sehat**: otorisasi berlapis kuat, audit trail lengkap, arsitektur middleware rapi, dan suite test hijau. **Tidak ada temuan Critical** pada review kode statis.

Namun QA gabungan (runtime + statis, total **23 temuan statis: 2 High · 8 Medium · 10 Low · 3 Nit**) menemukan:

- 🔴 **P0 — File sensitif dapat diunduh tanpa login** (terverifikasi live): dokumen intel unit, **foto DPO/buronan**, dan attachment masih tersimpan di `public/storage/badig/...` → bisa diunduh siapa pun lewat URL langsung (PDF laporan 881 KB, foto 201 KB, xls 43 KB — semua `200` tanpa sesi), sementara route resminya benar (302 → login).
- 🔴 **P0 — Email/OTP rusak di produksi**: SMTP 465/587 gagal + `proc_open()` dinonaktifkan di host (riwayat log prod).
- 🟠 **P0 — `.envv` membawa kredensial DB produksi dan tidak tercakup `.gitignore`** (pola `.env.*` tidak cocok dengan `.envv`).
- 🟠 2 bug frontend aktif: marker peta diblokir CSP, SyntaxError Alpine di dashboard.
- 🟠 Performa: TTFB server 0,85–1,26 s per halaman + PDF/backup sinkron dalam HTTP request.

| Aspek | Nilai | Catatan |
|---|---|---|
| Otorisasi & isolasi unit | 🟢 Sangat baik | Matriks GET+POST lintas unit 100% tertutup (403) |
| Audit & observability | 🟢 Sangat baik | LOGIN/FAILED/LOGOUT/ILLEGAL_ACCESS tercatat |
| Keamanan dasar (CSRF, throttle, header) | 🟢 Baik | 419 tanpa token; rate-limit terpasang |
| Aksesibilitas (Lighthouse) | 🟢 100 | |
| Suite test otomatis | 🟢 Hijau | `php artisan test` → 7 passed / 51 assertions |
| Performa runtime | 🟠 Sedang | LCP 1,4s ok, tapi TTFB server tinggi & CLS 0,18 |
| Stabilitas JS frontend | 🟠 Sedang | 2 bug aktif (Alpine dashboard, CSP marker) |
| Penyimpanan file | 🔴 Bocor | File legacy di `public/storage` tanpa proteksi |
| Email/OTP (produksi) | 🔴 Rusak | SMTP gagal, `proc_open` disabled |

**Kesimpulan:** Fitur inti (login, CRUD unit, file manager, backup, laporan PDF, database orang) berfungsi dan terlindungi di lapisan aplikasi. Tangani **P0 (Bagian 7)** segera — terutama perpindahan file keluar dari `public/storage`.

---

## 2. QA Runtime Browser (Chrome DevTools MCP)

### 2.1 Skenario yang diuji

| # | Skenario | Hasil | Bukti |
|---|---|---|---|
| 1 | Load halaman login | ✅ 200, semua aset sukses | 14 request, 0 gagal |
| 2 | Login Super Admin (`admin@badig.sardev.biz.id`) | ✅ Redirect ke /dashboard | |
| 3 | Login akun Unit 1 (`unit1@...`) | ✅ Redirect ke /dashboard, sidebar unit1 saja | Menu admin tidak tampil |
| 4 | Login password salah | ✅ Pesan generik "Email atau kata sandi tidak sesuai." | Tanpa user enumeration |
| 5 | Logout | ✅ Audit tercatat `LOGOUT` | |
| 6 | Matriks akses (GET) — lihat 2.2 | ✅ Semua 403 untuk di luar role | |
| 7 | Matriks akses (POST/tulis lintas unit) | ✅ 403 | |
| 8 | Login tanpa CSRF token | ✅ 419 | |
| 9 | Submit form create kosong | ✅ Diblokir validasi native (required) | 4 field `:invalid` |
| 10 | Filter aktivitas di dashboard | ⚠️ Fungsi jalan, indikator aktif mati | Bug JS (2.4) |
| 11 | Export PDF unit & database orang | ✅ 200 (PDF valid) | 2,6 KB & 880 KB |
| 12 | Health check `/up` | ✅ 200 | |
| 13 | Brute-force login ×7 (email beda) | ✅ Tidak ada 429 palsu; batas belum terlampaui sesuai desain (10/menit/email) | |

### 2.2 Matriks Otorisasi (akun Unit 1, role_id=1)

| Halaman | Status | Keterangan |
|---|---|---|
| `/data/unit1` (unit sendiri) | ✅ 200 | |
| `/data/unit2`, `/data/unit3` | ✅ 403 | "Unit ini berada di luar otorisasi Anda." |
| `POST /data/unit2`, `/data/unit3` | ✅ 403 | Tulis lintas unit ditolak middleware |
| `/admin-it/users`, `/admin-it/audit-trail` | ✅ 403 | Khusus role 5 |
| `/super-admin`, `/super-admin/units` | ✅ 403 | Khusus role 5 |
| `/database-orang` | ✅ 403 | Khusus role 3 & 5 |
| `/reports` | ✅ 403 | |
| `/profile` | ✅ 200 | |
| Tanpa sesi, semua `/dashboard`, `/data/*`, `/admin-it/*` | ✅ 302 → `/login` | |

> **Catatan positif:** percobaan akses ilegal langsung tercatat di audit trail sebagai `ILLEGAL_ACCESS_ATTEMPT` dengan nama route — keamanan defensif bekerja end-to-end.

### 2.3 Konsol, Network & Error JS

**Temuan:**
1. 🔴 **SyntaxError JS ×4 di dashboard** — `resources/views/dashboard/index.blade.php:102`. Kelas `badig-action badig-action-primary` tertulis **di luar tanda kutip** ekspresi ternary Alpine:
   ```blade
   :class="actionFilter === '{{ $key }}' ? 'border-accent ...' : 'border-line ... text-ink' badig-action badig-action-primary"
   ```
   → `Uncaught SyntaxError: Unexpected identifier 'badig'` + 4× `Alpine Expression Error`. Dampak: binding `:class` gagal total sehingga **tombol filter tidak pernah menampilkan status aktif** (cacat UX). Logika filter sendiri tetap berfungsi (x-show/actionFilter normal).
2. 🟠 **CSP memblokir ikon marker Leaflet** — `app/Http/Middleware/SecurityHeaders.php:81-85`. `script-src` mengizinkan `https://unpkg.com` (JS Leaflet jalan), tetapi `img-src` hanya `'self' data: blob: https://tile.openstreetmap.org` → `https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png` **diblokir**. Akibat: **marker peta tidak menampilkan ikon pin** di form create/unit (halaman peta tampak "kosong" tanpa penanda).
3. 🟡 **Warning "multiple instances of Alpine" di semua halaman** — Alpine di-bootstrap di `resources/js/app.js` (`.start()`) dan tampaknya dimuat pula oleh bundel Flux → 2 instance. Dampak minor (duplikasi inisialisasi), tapi perlu dirapikan.

Tidak ada request network yang gagal di semua halaman yang ditelusuri (semua 200/302/403 sesuai konteks).

### 2.4 Verifikasi fungsional filter dashboard

- Klik "Tambah/Perbarui/Hapus": baris tersaring benar (6 entri terakhir = seluruhnya `ILLEGAL_ACCESS_ATTEMPT`, jadi 0 baris per aksi — logika filter benar).
- Namun pada **semua tombol, status aktif tidak pernah tampil** (`border-accent` tidak pernah muncul) — sesuai temuan bug #1.

---

## 3. Performa (penyebab "lemot")

### 3.1 Pengukuran (warm cache, tanpa throttle)

| Halaman | TTFB (ms) | FCP (ms) | DCL (ms) | Load (ms) |
|---|---|---|---|---|
| /dashboard | 971–1190 | ~1420 | ~1437 | ~1444 |
| /database-orang | 1004 | 1208 | 1230 | 1243 |
| /data/unit1 | 865–1261 | 1488 | 1515 | **1048 (warm)** / **4634 (cold)** |
| /admin-it/users | 849 | 1028 | 1085 | 1098 |
| /super-admin | 1190 | 1420 | 1437 | 1444 |
| /admin-it/audit-trail | 994 | 1244 | 1169 | 1178 |
| /reports | 1044 | 1308 | 1214 | 1221 |
| /pimpinan/file-manager | 1206 | 1420 | 1453 | 1462 |
| 302 unauth (baseline boot) | ~450–490 | — | — | — |

### 3.2 Performance Trace (dashboard, Lighthouse/DevTools)

- **LCP 1435 ms** (`≤2500` = good) — tetapi **TTFB 971 ms = 68% dari LCP**; render delay 464 ms.
- **CLS 0,18–0,21** (`>0,1` = needs improvement) — ada layout shift (dugaan: font swap & konten yang muncul belakangan).
- Insight `DocumentLatency`: potensi hemat FCP/LCP **864 ms** bila respons server dipercepat; **63,9 kB wasted** (tanpa kompresi).
- Lighthouse: **A11y 100 · Best Practices 96 · SEO 69** (SEO rendah karena `robots.txt` sengaja memblokir halaman internal — **bukan masalah** untuk app internal).

### 3.3 Temuan performa

1. 🔴 **TTFB server tinggi (850–1260 ms) — penyebab utama kesan "lemot".** Boot framework ±450 ms + waktu auth/session/query ±400–800 ms per request. Di environment produksi (PHP-FPM + opcache, DB teroptimasi) angka ini bisa turun drastis, tapi tetap wajib diukur & diperbaiki: cek opcache, query N+1, index DB (lihat Bagian 6, MED-2/3 & LOW-8).
2. 🟠 **Tanpa kompresi & cache header di dev server.** `Content-Encoding` kosong pada semua respons; CSS build 278 KB dikirim mentah-mentah, tanpa `Cache-Control`/`ETag` (`curl -I` no headers). Di produksi pastikan gzip/brotli + cache-immutable untuk `/build/assets/*`.
3. 🟠 **Font eksternal menambah beban cold-load ~3,5 detik**; total transfer 382 KB (13 resource) — CSS 278 KB, logo 57 KB, JS 46 KB, font 5×woff2 + Material Symbols. First load `/data/unit1` = **4634 ms** sebelum cache hangat. Pertimbangkan self-host font (privacy + keandalan jaringan terbatas).
4. 🟡 **CLS > 0,1** — ukur ulang setelah font di-self-host & pastikan dimensi konten statis.
5. 🟡 Cache `no-store` — aman tapi membuat aset tak ter-cache oleh browser/proxy.

---

## 4. Keamanan

### 4.1 Yang sudah baik ✅
- **Header keamanan lengkap:** CSP nonce (`script-src 'self' 'nonce-…'`), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`.
- **CSRF aktif** (POST tanpa token → 419).
- **Rate limiting terbagi rapi** (`AppServiceProvider.php:26–53`): login 10/mnt/email+IP + 30/mnt/IP; tulis 30/mnt; export berat 5/mnt; import & backup per jam.
- **Otorisasi berlapis:** middleware role (`CheckRole`), isolasi unit (`UnitIsolation`), validasi konsistensi role-unit (`EnsureRoleUnitConsistency`), sesi segar (`EnsureSessionFresh` — logout otomatis saat akun terkunci/role-unit bermasalah, idle timeout sesuai `session.lifetime`).
- **Audit trail menyeluruh** termasuk percobaan akses ilegal.
- Password salah → pesan generik (anti user-enumeration). Cookie session `HttpOnly; SameSite=Lax; Secure`.
- Error login di-audit (`LOGIN_FAILED`).

### 4.2 Temuan
1. 🟡 `x-powered-by: PHP/8.4.25` ter-expose pada respons (file proses: lewat header default PHP/Laravel) — hapus di produksi (hilangkan `expose_php`, atau filter header).
2. 🟡 CSP mengizinkan `'unsafe-eval'` dan CDN eksternal (`cdn.jsdelivr.net`, `unpkg.com`, fonts) — diperlukan oleh Alpine/Leaflet saat ini; dikelola eksplisit + nonce. Risiko: ketersediaan bergantung CDN pihak ketiga untuk sistem intelijen internal (lihat #3 performa) + permukaan serangan. Prioritaskan self-host.
3. 🟢 `robots.txt` & `sitemap.xml` konfigurasi aman (blokir semua jalur internal; sitemap hanya publik) — bukan temuan, dicatat karena sempat terlihat sebagai "SEO 69".

---

## 5. Infrastruktur, Log & Suite Test

- **Log membesar:** `storage/logs/laravel.log` sudah **1,05 MB / ±4.948 baris**. Berisi riwayat produksi lama (`production.ERROR`) bercampur dev lokal. Wajib: rotation (Laravel daily), dan pisahkan environment.
- 🔴 **Produksi — email/OTP rusak (riwayat 30–31 Jul):**
  - `Connection could not be established with host "ssl://badig.sardev.biz.id:465"` (OpenSSL/SSL error) — berulang.
  - `Call to undefined function ... proc_open()` di `vendor/symfony/mailer/.../ProcessStream.php` — **fungsi PHP `proc_open` dinonaktifkan di host** → transport SMTP alternatif gagal.
  - SMTP auth `535 Incorrect authentication data` untuk `no-reply@badig.sardev.biz.id`; DNS `mail.badig.sardev.biz.id` tidak resolve.
  - Dampak: **OTP/email tidak terkirim di produksi** → fitur keamanan autentikasi lemah di prod. → **P0**.
- ✅ (riwayat) Fatal `Cannot use Illuminate\Support\Facades\Route as Route` di `routes/web.php:19` — sudah diperbaiki.
- ✅ **Suite test:** `php artisan test` → 7 passed / 51 assertions (1,82 s) — semua hijau. 70 route terdaftar.
- ✅ Tidak ada error baru selama sesi QA ini (semua alur 200/403/302 normal; akses ilegal tercatat sebagai `ILLEGAL_ACCESS_ATTEMPT`).

---

## 6. Review Kode Statis & Suite Test

> Hasil review read-only (`routes/web.php`, controllers, models, middleware, services, migrations, views) — setiap temuan diverifikasi dari kode (`file:baris`). **Uji: `php artisan test` → 7 passed / 51 assertions (hijau).**

### 6.1 Ringkasan
| Severity | Jumlah |
|---|---|
| Critical | 0 |
| High | 2 |
| Medium | 8 |
| Low | 10 |
| Nit | 3 |
| **Total** | **23** |

### 6.2 HIGH (keduanya terverifikasi live)
1. **File sensitif legacy terekspos publik** — `app/Services/SecureMediaStorage.php:13` (`LEGACY_PUBLIC_DISK`). 22 file di `public/storage/badig/...` (PDF laporan intel, foto DPO, attachment) bisa diunduh tanpa login lewat URL langsung; **diverifikasi `200`** untuk `arsip/unit1/1/report_…pdf` (881 KB), `database-buronan/2/foto-….jpeg` (201 KB), `…/attachments/….xls` (43 KB) — padahal route resmi tanpa sesi `302 → /login`. Nama file `report_{id}_{timestamp}.pdf` mudah ditebak.
2. **`.envv` = kredensial produksi, tidak ter-cover `.gitignore`** — `.envv:21-25` berisi `DB_DATABASE=adaloker_stpi`, `DB_USERNAME=adaloker_satria`, `DB_PASSWORD=…`, `APP_ENV=production`. `.gitignore:3-4` hanya `.env` dan `.env.*` (butuh titik setelah `.env`), jadi `.envv` **ikut ter-commit** bila repo di-init/push. Saat ini bukan git repo (risiko kondisional), tetapi higienya sudah salah.

### 6.3 MEDIUM
1. **PDF laporan dirender sinkron di tiap create/update** — `IntelUnitController.php:200,242` → `syncReportPdf()`; `:506-509` naikkan `memory_limit 512M` + `set_time_limit(300)`, `:528-537` baca semua lampiran ke data-URI + DOMPDF. Error ditelan senyap (`:568-570`). → Queue + status ke user.
2. **Dashboard muat hingga 8.000 titik + ±20 query/render** — `DashboardController.php:43-47` (4× count), `:67-89` (4 × `limit(2000)` + eager), `:91-108` (3 query/unit), `:103-105` (3× clone query). Payload bisa >3 MB di Super Admin. → Agregasi/cluster + cache.
3. **Introspeksi skema tiap request** — `Schema::hasTable/hasColumn` di `layouts/app.blade.php:49,54` (semua halaman), `IntelUnitController.php:72,131`, `UnitDocumentController.php:32`, `AuditLog.php:19` (per insert log!), `Unit.php:38`, `User.php:113`, dll. → cache/per-proses sekali.
4. **Export PDF "Data Orang" mengabaikan filter** — `database-orang/index.blade.php:15` meneruskan `request()->query()`, tapi `DatabaseOrangController.php:232-244` tidak membacanya (selalu `limit(1000)` tanpa filter) → PDF tidak sesuai hasil saringan user.
5. **Validasi unit4 tidak sinkron dengan skema** — `IntelUnitController.php:613` validasi `catatan_aktivitas` padahal kolom & `$fillable` tidak ada (`Unit4Security.php:15-19`) → dibuang senyap; sebaliknya `data_enkripsi` bisa diisi tapi tidak pernah dirender di show/PDF (MED-5).
6. **Kode mati / controller tak ter-rute** — `SuperAdminBackupController.php:21-29` `sprintf('mysqldump -u%s -p%s …')` + `exec()` (password terekspos `ps`, controller tak ter-rute); controller mati: `ArchiveController`, `GlobalSearchController`, `SecureDownloadController`, `FileManagerController`, `MasterFileManagerController`, `AuditLogController`; view me-render route yang tak ada: `welcome.blade.php:41` (`register`), `archives/index.blade.php:33` (`archives.export`), `file-manager/index.blade.php:33` (`files.upload`), `super-admin/units/create.blade.php:10` (`super-admin.units.store`).
7. **Duplikasi `$modules`** — `IntelUnitController.php:25-58` vs `UnitDocumentController.php:20-25` → drift field (sumber MED-5). Satu kelas konfigurasi bersama.
8. **Backup DB full-dump sinkron dalam request** — `BackupManagementController.php:31-68,70-147` menulis seluruh tabel baris-per-baris tanpa `set_time_limit`/queue → timeout/502 bila DB besar.

### 6.4 LOW
1. `UnitIsolation.php:20` hanya kenal `unit[1-4]` & route `{unit}` tanpa `where` constraint — nilai aneh lolos lapisan pertama (ditutup `IntelUnitController.php:68`, tapi lapisan sia-sia).
2. ID non-numerik → **500 TypeError**, bukan 404 — `IntelUnitController.php:206,216,228,248`, `UnitDocumentController.php:63,70` (`int $id` tanpa `whereNumber` di `routes/web.php:57-61`).
3. `reportPdf` memotong diam-diam: `limit(500)` tanpa info di UI/PDF (`IntelUnitController.php:392`).
4. Race `firstOrCreate` kecamatan/kelurahan manual (`IntelUnitController.php:479,489,497`) → duplikat/500.
5. OTP nonaktif default + tanpa lockout akun (`config/security.php:30`) — hanya rate limiter.
6. CSP `'unsafe-eval'` + CDN eksternal (`SecurityHeaders.php:81`) — melemahkan proteksi XSS, tergantung pihak ketiga (terkait temuan marker).
7. `User::$fillable` memuat `role_id/unit_id/is_locked/login_attempts/otp_code` (`User.php:21-35`) — belum tereksploit, tapi satu `$request->all()` di masa depan = privilege escalation.
8. Index kurang untuk sort/filter panjang; `audit_logs` tanpa index `created_at`/`user_id`; `Schema::hasTable` per insert log (`AuditLog.php:19`) → full scan + filesort.
9. Lampiran di-upload **setelah commit** transaksi (`IntelUnitController.php:191-199,235-241`) → row tanpa lampiran/file yatim bila gagal.
10. Layanan mati: `NotificationService.php:11-14` justru `where('role_id', 1)` (bukan 5/Super Admin) & tidak kirim apa pun; `SecureFileService`, `AuditService`, `HasAuditLog` tidak dipakai.

### 6.5 NIT
1. Livewire terpasang (`composer.json:14`) tetapi 0 `wire:` di views → beban aset tanpa guna. 2. Tiga `clone $query` di `DashboardController.php:103-105` bisa jadi satu agregat. 3. Fallback try/catch & `Schema::hasTable` di method stabil (`IntelUnitController.php:71-80`, `User.php:112-121`) mempersulit pembacaan.

### 6.6 Yang sudah baik (terverifikasi)
- Setiap endpoint punya cek (`authorizeUnit/authorizeRecord/authorizeAccess/authorizeSuperAdmin`) **plus** global scope `BelongsToUnit` (`app/Models/Concerns/BelongsToUnit.php:27-42`) sebagai pertahanan kedua.
- Tidak ada `$request->all()` langsung ke model; semua lewat `validate()` eksplisit — **no mass-assignment exploit**.
- Output aman: konten user di-`e()` sebelum `nl2br` — **no XSS**.
- `SecureMediaStorage` anti path-traversal (`:138-157`), normalisasi nama unduhan, MIME dibatasi, download & backup ber-throttle + regex whitelist filename.
- Test suite hijau (7/51).

---

## 7. Prioritas Perbaikan

| Prioritas | Item | Sumber | Dampak |
|---|---|---|---|
| **P0-1** | Pindahkan/hapus file legacy dari `public/storage/badig` (PDF laporan, foto DPO, attachment) | HIGH-1 (live: `200` tanpa login) | **Bocor data intel & DPO**; otorisasi dilewati; nama file mudah ditebak |
| **P0-2** | Perbaiki email/OTP produksi | Log prod (SMTP 465/587 + `proc_open` disabled) | OTP/notifikasi tidak terkirim → auth lemah |
| **P0-3** | Amankan `.envv` (hapus/pindah ke `.env`, tambah ke `.gitignore`) | HIGH-2 | Kebocoran kredensial DB produksi bila repo di-push |
| **P1-1** | CSP: tambah `unpkg.com` ke `img-src` atau self-host Leaflet | Runtime (marker peta tak tampil) | Fitur lokasi tidak berguna |
| **P1-2** | Fix SyntaxError Alpine `dashboard/index.blade.php:102` | Runtime (error console ×8) | Indikator filter aktif mati |
| **P1-3** | Pindahkan `syncReportPdf` & backup dump ke queue; tanda status ke user | MED-1, MED-8 | Create/backup lambat/timeout; error PDF senyap |
| **P1-4** | Batasi payload dashboard (agregasi/cluster + cache `count`) | MED-2, NIT-2 | TTFB & HTML membengkak seiring data |
| **P1-5** | Hapus introspeksi skema per-request (`Schema::hasTable/hasColumn`) | MED-3 | Latensi permanen di tiap request |
| **P1-6** | TTFB server 0,85–1,26 s | Runtime + MED-2/3 | Kesan "lemot" |
| **P2-1** | Export PDF "Data Orang" hormati filter index | MED-4 | Laporan salah/berlebih |
| **P2-2** | Sinkronkan validasi/kolom unit4 (`catatan_aktivitas`/`data_enkripsi`) & dedup `$modules` | MED-5, MED-7 | Data user hilang senyap; drift field |
| **P2-3** | Bersihkan kode mati: controller tak ter-rute, `exec(mysqldump … -pPassword)`, route-route phantom | MED-6 | Jebakan pemeliharaan; password terekspos `ps` |
| **P2-4** | Kompresi (gzip/brotli) + `Cache-Control: immutable` untuk `/build/assets` | Runtime | 278 KB CSS mentah; transfer boros |
| **P2-5** | Self-host font (Inter + Material Symbols) | Runtime | Cold-load −3,5 s; tak bergantung CDN; perbaiki CLS |
| **P2-6** | Alpine 1 instance (cek interop Flux) | Runtime | Warning semua halaman |
| **P3-1** | LOW-1..10 statis (unit-isolation pattern, ID non-numerik → 404, `limit(500)` tak transparan, race `firstOrCreate`, lockout akun, `$fillable` sensitif, index DB, upload di luar transaksi, layanan mati) | Statis | Hardening bertahap |
| **P3-2** | `expose_php`/header `x-powered-by`, rotation log & pisah env | Runtime | Info leak minor; diagnosa mudah |

---

*Laporan QA gabungan (runtime browser + audit backend + review kode statis). Data runtime dapat direproduksi (Bagian 2); bukti kode statis pada Bagian 6.*

---