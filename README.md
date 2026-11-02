# Booking Barbershop

Aplikasi booking barbershop — **monorepo** (Flutter + Laravel + dokumentasi).

**Repository:** [github.com/adhimaswisnuyudo/digitech-barbeshop](https://github.com/adhimaswisnuyudo/digitech-barbeshop)

| Bagian | Path | Stack |
|--------|------|--------|
| Mobile | [`mobile/`](mobile/) | Flutter (iOS & Android) |
| Backend | [`backend/`](backend/) | Laravel, MySQL, Sanctum |
| Docs | [`docs/`](docs/) | PRD, TODO, design |

## Dokumentasi

- [Dokumentasi produk](docs/PRD.md)
- [Keputusan produk](docs/DECISIONS.md)
- [Checklist implementasi](docs/TODO.md)
- [Design (warna & font)](docs/DESIGN.md)
- [API v1 (draft)](docs/API.md)
- [Setup akun & dev](docs/SETUP.md)

## Struktur monorepo

```
booking_barbershop/          ← root git (satu repository)
├── README.md
├── .gitignore
├── mobile/                  ← app Flutter
├── backend/                 ← API Laravel
└── docs/
```

Backend dan mobile **tidak** di repository terpisah; release/versioning koordinasi via tag monorepo atau changelog per folder (sesuai kebiasaan tim).

## Prasyarat dev

- Flutter SDK (stable)
- PHP 8.2+, Composer
- MySQL 8+
- Node (opsional, jika tooling Laravel/Vite)

## Quick start (setelah scaffold)

```bash
# Backend
cd backend
cp .env.example .env
composer install
php artisan migrate --seed
php artisan serve

# Mobile
cd mobile
cp env.example.json env.json   # edit API_BASE_URL & Google IDs
flutter pub get
flutter run --dart-define-from-file=env.json
```

Buat database MySQL `booking_barbershop` terlebih dahulu — lihat [docs/SETUP.md](docs/SETUP.md).

Sesuaikan `API_BASE_URL` di `mobile/env.json`.

## Lisensi

TBD
