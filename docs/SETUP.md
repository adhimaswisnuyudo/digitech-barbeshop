# Setup akun & lingkungan dev

Checklist Fase 0 — centang saat selesai di organisasi Anda.

Acuan: [README.md](../README.md) · [DECISIONS.md](./DECISIONS.md)

---

## 1. Repository

- [x] Git init di root monorepo
- [x] Remote: `git remote add origin https://github.com/adhimaswisnuyudo/digitech-barbeshop.git`
- [x] Push pertama ke `origin/main` (commit monorepo Fase 0)

---

## 2. MySQL lokal

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS booking_barbershop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Backend:

```bash
cd backend
cp .env.example .env   # jika belum
# Sesuaikan DB_USERNAME / DB_PASSWORD
php artisan key:generate
php artisan migrate
```

> Instalasi awal Laravel mungkin memakai SQLite di `.env` lokal; salin pengaturan **MySQL** dari `.env.example`.

---

## 3. Google Cloud — Sign in with Google

- [ ] Buat project di [Google Cloud Console](https://console.cloud.google.com/)
- [ ] OAuth consent screen (External / Internal)
- [ ] Credentials → **OAuth 2.0 Client ID**:
  - [ ] **Web client** → `GOOGLE_CLIENT_ID` / `GOOGLE_WEB_CLIENT_ID` (verifikasi `id_token` di Laravel)
  - [ ] **Android** → package `com.bookingbarbershop.booking_barbershop` + SHA-1 debug/release
  - [ ] **iOS** → bundle ID `com.bookingbarbershop.bookingBarbershop`
- [ ] Isi `backend/.env`: `GOOGLE_CLIENT_ID=...`
- [ ] Salin `mobile/env.example.json` → `mobile/env.json` (gitignored) dan isi client IDs

---

## 4. Firebase — FCM push

- [ ] Buat project [Firebase Console](https://console.firebase.google.com/) (bisa link ke GCP yang sama)
- [ ] Tambah app **Android** & **iOS** ke project
- [ ] Download `google-services.json` → `mobile/android/app/`
- [ ] Download `GoogleService-Info.plist` → `mobile/ios/Runner/`
- [ ] Cloud Messaging: siapkan **Server key** atau service account untuk Laravel → `FCM_SERVER_KEY` / credentials di `backend/.env`
- [ ] Isi `FIREBASE_PROJECT_ID` di `mobile/env.json`

---

## 5. API staging (opsional Fase 0)

- [ ] Host Laravel (VPS, Laravel Forge, Railway, dll.)
- [ ] HTTPS + domain (mis. `api.staging.example.com`)
- [ ] Update `API_BASE_URL` di mobile env untuk tim QA

---

## 6. Flutter run

```bash
cd mobile
cp env.example.json env.json   # edit URL & IDs
flutter pub get
flutter run --dart-define-from-file=env.json
```

Emulator Android: `API_BASE_URL=http://10.0.2.2:8000/api/v1`

---

## Riwayat

| Tanggal | Catatan |
|---------|---------|
| 2025-11-02 | Checklist Fase 0 |
