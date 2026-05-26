# AGENTS

## Keutamaan Pengguna

- Gunakan Bahasa Melayu untuk komunikasi dengan pengguna.
- Elakkan bertanya soalan jika anda boleh terus laksanakan tugasan dengan andaian munasabah.

## Git Workflow

- Selepas siap kerja, **WAJIB jalankan `npm run build`** dahulu.
- Jika build berjaya, teruskan `git add`, `git commit`, dan `git push` tanpa meminta pengesahan tambahan.
- Tulis mesej commit dalam Bahasa Melayu — ringkas, padu, deskriptif.

## Stack & Perkakasan

- **Laravel 13** (PHP ^8.3), **Tailwind CSS v4**, **Inertia.js**, **React 19**, **TypeScript**, **Vite**.
- Tiada `tailwind.config.js` — konfigurasi Tailwind v4 berada dalam `resources/css/app.css` melalui direktif `@theme`.
- Build frontend: `npm run build`. Dev server: `npm run dev`.
- Dev penuh Laravel: `composer dev` (menjalankan `artisan serve`, `queue:listen`, `pail`, dan `vite` secara serentak melalui `concurrently`).
- Entry point frontend: `resources/js/app.tsx`.
- Root view Inertia: `resources/views/app.blade.php`.
- Layout React: `resources/js/Components/Layouts/FrontendLayout.tsx`, `MemberLayout.tsx`, `AdminLayout.tsx`.

## Pangkalan Data

- `.env` semasa menggunakan **MySQL** (`DB_CONNECTION=mysql`), tetapi `.env.example` default kepada **SQLite**.
- Ujian (`php artisan test`) menggunakan SQLite `:memory:` — lihat `phpunit.xml`.
- Seeder mencipta admin: `admin@sticker.com` / `password`.

## Ujian & Kualiti Kod

- Jalankan ujian: `composer test` (atau `php artisan test`).
- `composer test` akan memastikan `config:clear` dijalankan terlebih dahulu.
- Laravel Pint tersedia untuk formatting (`vendor/bin/pint`).

## Arkitektur Utama

- **Role-based**: `User` mempunyai atribut `is_admin` (boolean). Middleware `admin` melindungi laluan `admin.*`.
- **Namespace Pengawal**:
  - `App\Http\Controllers\Admin\*` — pentadbir
  - `App\Http\Controllers\Member\*` — ahli berdaftar
  - `App\Http\Controllers\*` — umum / frontend
- **Model penting**: `Order`, `OrderItem`, `Invoice`, `StickerDesign`, `StickerSize`, `StickerPriceTier`, `CustomerAddress`.
- **Integrasi**:
  - Google OAuth (`laravel/socialite`) — login ahli.
  - J&T Express API — penghasilan waybill & tracking (lihat `Admin\JntController`).
  - Google Contacts API — pengurusan senarai kenalan (lihat `Admin\GoogleContactController`).
  - OpenAI API — untuk ciri AI (lihat env `OPENAI_API_KEY`).
- **MCP Server**: Terdapat server MCP dalam `app/Mcp/` (lihat `StickerStoreServer` dan tools seperti `GetStickerPriceTool`).

## Pembolehubah Alam Penting

- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` — untuk OAuth.
- `JNT_BASE_URL`, `JNT_API_ACCOUNT`, `JNT_PRIVATE_KEY`, `JNT_CUSTOMER_CODE`, `JNT_PASSWORD`, `JNT_SENDER_*` — untuk penghantaran J&T.
- `OPENAI_API_KEY`, `OPENAI_MODEL` — untuk ciri AI.

## Nota Pembangunan

- Blade layouts utama: `resources/views/layouts/frontend.blade.php` dan `resources/views/layouts/admin.blade.php` (sedang dipindah ke React Layouts).
- Root view Inertia: `resources/views/app.blade.php`.
- Layout React: `resources/js/Components/Layouts/FrontendLayout.tsx`, `MemberLayout.tsx`, `AdminLayout.tsx`.
- CSS custom class untuk admin (`admin-*`) dan frontend (`frontend-*`) didefinisikan dalam `resources/css/app.css`.
- Seeder (`DatabaseSeeder`) memasukkan data asas kategori, design, dan harga sticker — jangan ubah struktur harga tanpa memahami matriks dalam seeder.
