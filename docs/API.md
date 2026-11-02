# API v1 — Booking Barbershop

Acuan: [PRD.md](./PRD.md) · Implementasi: `backend/` (Laravel)

**Base URL:** `{APP_URL}/api/v1`  
**Auth:** `Authorization: Bearer {sanctum_token}`  
**Content-Type:** `application/json`  
**ID:** semua identifier **UUID string**

---

## Format respons

### Sukses

```json
{
  "data": { }
}
```

List:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 0
  }
}
```

### Error

```json
{
  "message": "Human readable message",
  "code": "ERROR_CODE",
  "errors": {
    "field": ["validation message"]
  }
}
```

| HTTP | Penggunaan |
|------|------------|
| 401 | Token invalid / tidak login |
| 403 | Role tidak allowed |
| 422 | Validasi / bisnis (termasuk `WHATSAPP_REQUIRED`) |
| 409 | Konflik slot booking |

---

## Auth & profil

### POST `/auth/register`

**Public** — customer only.

**Body**

| Field | Tipe | Wajib |
|-------|------|-------|
| `name` | string | ya |
| `email` | string | ya |
| `password` | string | ya |
| `password_confirmation` | string | ya |
| `whatsapp_number` | string | ya |

**Response 201**

```json
{
  "data": {
    "user": { "id": "uuid", "name": "...", "email": "...", "whatsapp_number": "+628..." },
    "token": "plainTextToken"
  }
}
```

---

### POST `/auth/login`

**Body:** `email`, `password`

**Response 200:** `user` + `token`

---

### POST `/auth/google`

**Body**

| Field | Tipe |
|-------|------|
| `id_token` | string (Google ID token) |

**Response 200:** `user`, `token`, `requires_whatsapp` (boolean)

---

### POST `/auth/logout`

**Auth required** — revoke current token.

---

### POST `/auth/forgot-password` (Should)

**Body:** `email`

---

### GET `/profile`

**Auth required**

```json
{
  "data": {
    "id": "uuid",
    "role": "customer",
    "name": "Nama Lengkap",
    "email": "a@b.com",
    "whatsapp_number": "+6281234567890",
    "avatar_url": null
  }
}
```

---

### PUT `/profile`

**Body (partial):** `name`, `whatsapp_number`, `avatar` (multipart — implementasi)

---

## Cabang & layanan

### GET `/branches`

**Public** (atau auth optional). Hanya `is_active: true`.

```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Cabang Pusat",
      "address": "Jl. ...",
      "latitude": -6.2,
      "longitude": 106.8,
      "opening_hours": {
        "monday": { "open": "09:00", "close": "21:00" }
      },
      "timezone": "Asia/Jakarta"
    }
  ]
}
```

---

### GET `/branches/{id}`

Detail satu cabang.

---

### GET `/services`

**Query:** `branch_id` (uuid, optional jika layanan global)

```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Potong rambut",
      "description": "...",
      "duration_minutes": 30,
      "price": 50000,
      "is_active": true
    }
  ]
}
```

---

## Availability

### GET `/availability`

**Query**

| Param | Wajib | Keterangan |
|-------|-------|------------|
| `branch_id` | ya | UUID |
| `service_id` | ya | UUID |
| `date` | ya | `YYYY-MM-DD` |
| `barber_id` | tidak | UUID; kosong = any barber |

**Response**

```json
{
  "data": {
    "date": "2025-11-10",
    "slots": [
      {
        "start_at": "2025-11-10T09:00:00+07:00",
        "end_at": "2025-11-10T09:35:00+07:00",
        "barber_id": "uuid"
      }
    ]
  }
}
```

---

## Bookings

### POST `/bookings`

**Auth:** customer · **Gate:** `whatsapp_number` harus terisi.

**Body**

| Field | Wajib |
|-------|-------|
| `branch_id` | ya |
| `service_id` | ya |
| `start_at` | ya (ISO 8601) |
| `barber_id` | tidak (null = any available) |
| `notes` | tidak |

**Response 201**

```json
{
  "data": {
    "id": "uuid",
    "status": "confirmed",
    "payment_method": "pay_at_shop",
    "start_at": "...",
    "end_at": "...",
    "branch": { },
    "service": { },
    "barber": { },
    "created_at": "...",
    "created_by": "Nama Customer"
  }
}
```

**422 `WHATSAPP_REQUIRED`**

```json
{
  "message": "Nomor WhatsApp wajib diisi sebelum booking.",
  "code": "WHATSAPP_REQUIRED"
}
```

---

### GET `/bookings`

**Auth** — scope by role:

- `customer`: booking sendiri
- `staff`: cabang/barber assigned
- `admin`: semua

**Query:** `status`, `date`, `branch_id`, `page`

---

### GET `/bookings/{id}`

Detail + relasi.

---

### POST `/bookings/{id}/cancel`

**Customer** (within policy) / **admin**.

**Body (optional):** `reason`

---

### PATCH `/bookings/{id}/status`

**Staff / admin**

**Body**

| Field | Values |
|-------|--------|
| `status` | `in_progress`, `completed`, `no_show`, `cancelled` (admin) |

---

## Admin (`/admin/*`)

**Auth:** role `admin` · Prefix: `/api/v1/admin`

| Method | Path | Fungsi |
|--------|------|--------|
| GET/POST/PUT/DELETE | `/branches` | CRUD cabang (+ lat/lng) |
| GET/POST/PUT/DELETE | `/services` | CRUD layanan |
| GET/POST/PUT/DELETE | `/barbers` | CRUD barber |
| PUT | `/barbers/{id}/schedules` | Jam kerja |
| POST/DELETE | `/barbers/{id}/time-off` | Libur |
| POST | `/bookings` | Booking manual |
| PATCH | `/bookings/{id}` | Reschedule |
| GET | `/dashboard` | Stat hari ini / minggu |

Struktur body admin mengikuti entitas §8 PRD; blameable diisi server-side.

---

## Device token (FCM)

### POST `/devices`

**Auth required**

**Body:** `fcm_token`, `platform` (`ios` | `android`)

---

## Blameable di JSON

Resource yang mutable menyertakan (read):

```json
"created_at": "2025-11-02T10:00:00+07:00",
"created_by": "Nama Lengkap",
"updated_at": "2025-11-02T11:00:00+07:00",
"updated_by": "Nama Lengkap"
```

---

## Riwayat

| Versi | Tanggal | Catatan |
|-------|---------|---------|
| 0.1 | 2025-11-02 | Draft kontrak Fase 0 |
