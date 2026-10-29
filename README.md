# MKK - Parkir Digital

Sistem Manajemen Parkir Digital Terintegrasi (Kiosk Tiket Masuk, Scanner Kamera Gerbang, Kasir Keluar, Manajemen Member, dan Laporan Rekapitulasi).

## Arsitektur Project
* **Backend**: Laravel 12 (REST API, Laravel Sanctum, Migrate-Only Database)
* **Frontend**: Nuxt v4 (Vue 3, Modern Tailored CSS, Zero-Emoji Enterprise UI)

## Menjalankan Backend (Laravel 12)
1. Masuk ke folder backend:
   ```bash
   cd backend
   ```
2. Copy konfigurasi environment jika belum ada:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
3. Jalankan migrasi dan seeder database:
   ```bash
   php artisan migrate --seed
   ```
4. Jalankan server backend:
   ```bash
   php artisan serve
   ```
   Backend berjalan di: `http://127.0.0.1:8000`

### Akun Default Seeder:
* **Super Admin**: `admin@parkir.com` | Password: `password`
* **Petugas**: `petugas@parkir.com` | Password: `password`

---

## Menjalankan Frontend (Nuxt v4)
1. Masuk ke folder frontend:
   ```bash
   cd frontend
   ```
2. Install dependensi:
   ```bash
   npm install
   ```
3. Jalankan development server:
   ```bash
   npm run dev
   ```
   Frontend berjalan di: `http://localhost:3000`
