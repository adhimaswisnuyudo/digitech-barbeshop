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

- [ ] Init Laravel di `backend/`
- [ ] Konfigurasi Sanctum untuk mobile API
- [ ] Prefix route `/api/v1`, JSON exception handler konsisten
- [ ] Timezone `Asia/Jakarta`, locale `id`
- [ ] `.env` / config database **MySQL** (`DB_CONNECTION=mysql`)
- [ ] CORS untuk Flutter dev

### 1.2 Konvensi data (UUID + blameable)

- [ ] Trait `RecordsBlameable` (isi `created_by` / `updated_by` dari `auth()->user()->name`, sistem = `System`)
- [ ] Base model / migration helper: UUID PK + 4 kolom blameable di setiap tabel
- [ ] Seed user admin bootstrap + 1 cabang contoh (opsional)

### 1.3 Migrasi & model inti

- [ ] Migration + model: `users` (role, email, password, google_sub, name, whatsapp_number, branch_id)
- [ ] Migration + model: `branches` (address, lat, lng, opening_hours, timezone, is_active)
- [ ] Migration + model: `services`
- [ ] Migration + model: `barbers`, `barber_schedules`, `barber_time_off`
- [ ] Migration + model: `bookings`, `booking_services`
- [ ] Migration + model: `notifications` (atau fase notifikasi)
- [ ] Factory & seeder data demo (1 cabang, 3 layanan, 2 barber, jadwal)

### 1.4 Auth & profil API

- [ ] `POST /auth/register` (customer + WhatsApp + blameable)
- [ ] `POST /auth/login`
- [ ] `POST /auth/google` (verifikasi ID token)
- [ ] `POST /auth/logout`
- [ ] `GET/PUT /profile` (gate WhatsApp format E.164, unik)
- [ ] Middleware RBAC: `customer`, `staff`, `admin`
- [ ] Policy: staff hanya akses cabang/barber assigned
- [ ] Should: `POST /auth/forgot-password`

---

## Fase 2 — Backend Laravel ( fitur bisnis )

### 2.1 Cabang & layanan

- [ ] `GET /branches`, `GET /branches/{id}` (publik/customer)
- [ ] `GET /services` (hanya aktif)
- [ ] Admin CRUD cabang (lat/lng wajib jika aktif)
- [ ] Admin CRUD layanan

### 2.2 Availability & booking

- [ ] Service/logic: hitung slot (jam buka cabang + jadwal barber + durasi + buffer 5 menit − booking existing)
- [ ] `GET /availability`
- [ ] `POST /bookings` (auto-confirm, tolak bentrok, gate `WHATSAPP_REQUIRED`)
- [ ] `GET /bookings`, `GET /bookings/{id}` (scope per role)
- [ ] `POST /bookings/{id}/cancel` (kebijakan 2 jam customer)
- [ ] `PATCH /bookings/{id}/status` (staff: in_progress, completed, no_show)
- [ ] Admin: buat manual, reschedule, cancel override

### 2.3 Admin & dashboard

- [ ] Admin CRUD barber + link user staff
- [ ] Admin kelola `barber_schedules` & `barber_time_off`
- [ ] `GET /admin/dashboard` — booking hari ini, occupancy mingguan, no-show rate
- [ ] Should: konfigurasi offset reminder (setting sederhana)

### 2.4 Notifikasi

- [ ] Integrasi FCM (simpan device token user)
- [ ] Queue job: push saat booking confirmed
- [ ] Scheduled job: reminder (24 jam & 2 jam — sesuai config)
- [ ] Should: push saat admin reschedule/cancel

### 2.5 Kualitas backend

- [ ] Feature tests: auth, availability, double-book ditolak, RBAC
- [ ] PHPUnit/Pest untuk blameable & UUID di response JSON
- [ ] Deploy staging + dokumentasi run migration/seed

---

## Fase 3 — Mobile Flutter

### 3.1 Project setup

- [ ] Init Flutter di `mobile/`
- [ ] Struktur folder: `core/`, `features/`, `data/`, `domain/` (atau konvensi setara)
- [ ] API client (Dio/http) + interceptor token Sanctum
- [ ] Routing (Bahasa Indonesia)
- [ ] `AppColors` + `ThemeData` + **Plus Jakarta Sans** sesuai [DESIGN.md](./DESIGN.md)
- [ ] Env: base URL API, Google Sign-In client IDs (iOS/Android)

### 3.2 Auth & profil (customer)

- [ ] Layar login & registrasi email/password
- [ ] Google Sign-In → `POST /auth/google`
- [ ] Layar lengkapi WhatsApp (gate global sebelum booking)
- [ ] Layar profil (edit nama, WhatsApp)
- [ ] Persistensi token aman (flutter_secure_storage)

### 3.3 Customer — cabang & peta

- [ ] Daftar / detail cabang
- [ ] Integrasi `flutter_map` + tile OSM + marker
- [ ] Atribusi OSM di UI
- [ ] Should: tombol buka navigasi eksternal (maps/Waze)
- [ ] Fallback alamat teks jika tile gagal

### 3.4 Customer — booking

- [ ] Katalog layanan
- [ ] Pilih barber (spesifik / any available)
- [ ] Kalender / picker slot dari API availability
- [ ] Konfirmasi booking (Bayar di tempat)
- [ ] Riwayat booking + cancel

### 3.5 Staff

- [ ] Login staff (email/password)
- [ ] Jadwal hari ini + detail booking (termasuk WhatsApp pelanggan)
- [ ] Aksi status: mulai, selesai, no-show

### 3.6 Admin (role di app Flutter — MVP)

- [ ] Login admin
- [ ] CRUD layanan & cabang (termasuk lat/lng)
- [ ] Kelola barber & jadwal (minimal)
- [ ] Daftar semua booking + override cancel/reschedule
- [ ] Dashboard ringkas (angka dari API)

### 3.7 Push notification

- [ ] Setup Firebase di Flutter (iOS + Android)
- [ ] Register FCM token ke backend
- [ ] Handle notifikasi foreground/background (buka detail booking)

### 3.8 Kualitas mobile

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
| 2025-11-02 | Monorepo: README + .gitignore root |
