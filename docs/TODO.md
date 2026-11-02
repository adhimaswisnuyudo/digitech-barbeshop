# Todo List — Booking Barbershop

Acuan: [PRD.md](./PRD.md)

**Cara pakai:** centang `[x]` saat selesai. Urutan fase disarankan top → bottom; item dalam fase bisa paralel jika tim terpisah (backend / mobile).

---

## Fase 0 — Persiapan & keputusan

- [x] Konfirmasi open questions — [DECISIONS.md](./DECISIONS.md)
- [x] Tentukan DB production: **MySQL**
- [x] Palet warna UI: primary `#404040`, secondary `#5E5E5E` — lihat [DESIGN.md](./DESIGN.md)
- [x] Struktur **monorepo** tunggal: root `booking_barbershop/` + `backend/`, `mobile/`, `docs/` ([README.md](../README.md))
- [x] Root `README.md` + `.gitignore` monorepo
- [x] Init git di root (`git init`)
- [x] Remote origin: `https://github.com/adhimaswisnuyudo/digitech-barbeshop.git`
- [x] Scaffold `backend/` (Laravel 13) & `mobile/` (Flutter)
- [x] `.env.example` backend & `mobile/env.example.json` + [app_config.dart](../mobile/lib/core/config/app_config.dart)
- [ ] Daftar akun eksternal — checklist [SETUP.md](./SETUP.md) §3–§5
- [x] [API.md](./API.md) draft kontrak v1

---

## Fase 1 — Backend Laravel ( fondasi )

### 1.1 Project setup

- [x] Init Laravel di `backend/`
- [x] Konfigurasi Sanctum untuk mobile API
- [x] Prefix route `/api/v1`, JSON exception handler konsisten
- [x] Timezone `Asia/Jakarta`, locale `id` (`.env.example`)
- [x] `.env` / config database **MySQL** (`DB_CONNECTION=mysql` di `.env.example`)
- [x] CORS default Laravel (native Flutter tidak wajib; siap untuk web client)

### 1.2 Konvensi data (UUID + blameable)

- [x] Trait `RecordsBlameable` + `BlameableColumns` migration helper
- [x] Base model UUID + blameable
- [x] Seed admin + 1 cabang demo + 2 barber + 3 layanan

### 1.3 Migrasi & model inti

- [x] `users`, `branches`, `services`, `barbers`, `barber_schedules`, `barber_time_off`
- [x] `bookings`, `booking_services`, `notifications`
- [x] Factory & seeder demo

### 1.4 Auth & profil API

- [x] `POST /auth/register`, `/login`, `/google`, `/logout`
- [x] `GET/PUT /profile` (WhatsApp E.164 + unik)
- [x] Middleware alias `role` + `BarberPolicy` (staff scope cabang/barber)
- [ ] Should: `POST /auth/forgot-password`

---

## Fase 2 — Backend Laravel ( fitur bisnis )

### 2.1 Cabang & layanan

- [x] `GET /branches`, `GET /branches/{id}` (publik)
- [x] `GET /services` (hanya aktif)
- [x] Admin CRUD cabang (lat/lng wajib jika aktif)
- [x] Admin CRUD layanan

### 2.2 Availability & booking

- [x] `AvailabilityService` (jam buka + jadwal barber + buffer 5 menit)
- [x] `GET /availability`
- [x] `POST /bookings` (auto-confirm, bentrok ditolak, `WHATSAPP_REQUIRED`)
- [x] `GET /bookings`, `GET /bookings/{id}` (scope role)
- [x] `POST /bookings/{id}/cancel` (kebijakan 2 jam customer)
- [x] `PATCH /bookings/{id}/status` (staff/admin)
- [x] Admin: booking manual + `PATCH /admin/bookings/{id}` reschedule

### 2.3 Admin & dashboard

- [x] Admin CRUD barber + jadwal + time off
- [x] `GET /admin/dashboard`
- [ ] Should: konfigurasi offset reminder via env (`BOOKING_REMINDER_HOURS`)

### 2.4 Notifikasi

- [x] `POST /devices` (FCM token)
- [x] Job push booking confirmed (+ log jika `FCM_SERVER_KEY` kosong)
- [x] Command `bookings:send-reminders` + schedule 15 menit
- [ ] Should: push saat admin reschedule/cancel

### 2.5 Kualitas backend

- [x] Feature tests: availability, double-book, WhatsApp gate, RBAC admin
- [x] `php artisan test` (9 tests)
- [ ] Deploy staging + dokumentasi ops (manual)

---

## Fase 3 — Mobile Flutter

### 3.1 Project setup

- [x] Init Flutter di `mobile/`
- [x] Struktur folder: `core/`, `features/`, `data/` (+ model domain ringan)
- [x] API client (Dio) + interceptor token Sanctum
- [x] Routing (Bahasa Indonesia) + redirect auth/WhatsApp
- [x] `AppColors` + `ThemeData` + **Plus Jakarta Sans** sesuai [DESIGN.md](./DESIGN.md)
- [x] Env: `env.example.json` + `--dart-define-from-file` (base URL, Google, Firebase id)

### 3.2 Auth & profil (customer)

- [x] Layar login & registrasi email/password
- [x] Google Sign-In → `POST /auth/google` (butuh client IDs di env)
- [x] Layar lengkapi WhatsApp (gate global sebelum booking)
- [x] Layar profil (edit nama, WhatsApp)
- [x] Persistensi token aman (flutter_secure_storage)

### 3.3 Customer — cabang & peta

- [x] Daftar / detail cabang
- [x] Integrasi `flutter_map` + tile OSM + marker
- [x] Atribusi OSM di UI
- [x] Should: tombol buka navigasi eksternal (maps/Waze)
- [x] Fallback alamat teks jika tile gagal

### 3.4 Customer — booking

- [x] Katalog layanan
- [x] Pilih barber (spesifik / any available)
- [x] Kalender / picker slot dari API availability
- [x] Konfirmasi booking (Bayar di tempat)
- [x] Riwayat booking + cancel

### 3.5 Staff

- [x] Login staff (email/password)
- [x] Jadwal hari ini + detail booking (termasuk WhatsApp pelanggan)
- [x] Aksi status: mulai, selesai, no-show

### 3.6 Admin (role di app Flutter — MVP)

- [x] Login admin
- [x] Daftar layanan (aktif/nonaktif) + daftar cabang (read-only lat/lng)
- [ ] Should: form tambah/edit cabang & barber di app
- [x] Daftar semua booking + cancel (admin)
- [x] Dashboard ringkas (angka dari API)

### 3.7 Push notification

- [x] Helper `PushRegistration` → `POST /devices` (siap dipanggil dengan token FCM)
- [ ] Setup Firebase di Flutter (iOS + Android) + `firebase_messaging`
- [ ] Register FCM token otomatis setelah login
- [ ] Handle notifikasi foreground/background (buka detail booking)

### 3.8 Kualitas mobile

- [x] Smoke widget test (layar login)
- [ ] Widget/integration test kritikal (auth gate, booking flow mock API)
- [ ] Build release internal (TestFlight / internal app sharing)

---

## Fase 4 — Integrasi, QA & rilis MVP

- [ ] End-to-end test staging: customer booking → staff selesai → admin dashboard
- [ ] Uji double-book & cancel policy
- [ ] Uji Google login + WhatsApp gate
- [ ] Uji peta OSM di perangkat nyata
- [ ] Privacy policy URL + teks permission push
- [ ] Prepare store listing (screenshot, deskripsi ID)
- [ ] Monitoring dasar API (log error, health check)
- [ ] Go-live MVP checklist

---

## Fase 5 — Backlog (post-MVP, dari PRD roadmap)

- [ ] Payment gateway (Midtrans/Xendit)
- [ ] WhatsApp Business API (reminder/konfirmasi otomatis)
- [ ] Web admin Laravel Filament
- [ ] Multi-layanan per booking
- [ ] Email verification
- [ ] In-app notification inbox
- [ ] Rating & review
- [ ] Multi-cabang UX polish (peta multi-marker)
- [ ] `docs/API.md` versi lengkap + OpenAPI/Swagger (opsional)

---

## Ringkasan prioritas Sprint 1 (Must)

| Area | Item kunci |
|------|------------|
| Backend | UUID/blameable, auth + Google, branches/services, availability, booking, RBAC |
| Mobile | Auth + WhatsApp gate, OSM cabang, flow booking, staff status |
| Ops | FCM confirmed + 1 reminder, staging deploy |

---

## Riwayat

| Tanggal | Catatan |
|---------|---------|
| 2025-11-02 | Todo list awal dibuat |
| 2025-11-02 | MySQL; warna `#404040` / `#5E5E5E` |
| 2025-11-02 | Fase 3 mobile: scaffold Flutter + analyze/test hijau; admin list layanan/cabang/booking |
