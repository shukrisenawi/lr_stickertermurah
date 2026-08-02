# AGENTS

## Commands

- Fresh setup: `composer run setup` (install, buat `.env`, key, migrate, npm install, build).
- Full local stack: `composer run dev` (Laravel server, queue listener, Pail, dan Vite).
- Frontend check: `npm run typecheck`; release verification: `npm run build` (`tsc` kemudian `vite build`).
- Tests: `composer test` atau `php artisan test`; satu fail: `php artisan test tests/Feature/AdminLoginBrandingTest.php`.
- Format PHP dengan `vendor/bin/pint`.
- Sebelum commit wajib berjaya `npm run build`; selepas itu commit dan push terus. Mesej commit Bahasa Melayu yang ringkas.

## Runtime & Tests

- Stack ialah Laravel 13, PHP `^8.3`, Inertia React 19, TypeScript, Vite 8, dan Tailwind CSS 4.
- `.env.example` default SQLite, tetapi runtime tempatan boleh menggunakan MySQL; jangan anggap pangkalan data `.env` sama dengan ujian.
- `phpunit.xml` secara eksplisit memaksa SQLite `:memory:` serta cache/session/queue/mail in-memory, jadi ujian tidak memerlukan MySQL.
- Seeder admin semasa ialah `admin@sticker` dengan kata laluan `123`; jangan salin kredensial lama daripada dokumentasi.

## Structure

- Entry frontend ialah `resources/js/app.tsx`; Vite membina `resources/css/app.css` dan entry itu. Alias TypeScript utama ialah `@/*`, `@components/*`, `@pages/*`, `@hooks/*`, `@lib/*`, dan `@types/*`.
- Halaman Inertia berada di `resources/js/Pages`; layout dikongsi melalui `Components/Layouts/{FrontendLayout,MemberLayout,AdminLayout}.tsx`.
- Route umum/member berada di `routes/web.php`; route admin menggunakan prefix `admin`, middleware `auth` + `admin`; controller dipisah kepada namespace `Admin`, `Member`, dan umum.
- Logik domain utama menggunakan `Order`, `Invoice`, `InvoicePayment`, `StickerDesign`, `StickerSize`, `PriceSetting`, dan `CustomerAddress`; perubahan harga perlu disemak bersama migration dan `DatabaseSeeder`.
- Tailwind 4 dikonfigurasi dalam `resources/css/app.css` melalui `@theme`; tiada `tailwind.config.js`.

## Important Gotchas

- Galeri home ialah data statik dalam `resources/js/data/showcase.ts` dengan imej `public/images/showcase/sticker-01.webp` hingga `sticker-35.webp`; ia bukan data design DB. `FrontendController::home()` hanya menyediakan testimonials.
- `StickerSize` mesti mengekalkan cast `float` untuk `width_cm` dan `height_cm`; DECIMAL MySQL boleh sampai ke frontend sebagai string.
- Integrasi OAuth Google, OpenAI, dan J&T bergantung pada env dalam `.env.example` (`GOOGLE_*`, `OPENAI_*`, `JNT_*`); jangan letakkan secret dalam repo.
- MCP server dan tools aplikasi berada di `app/Mcp/`; semaknya sebelum mengubah integrasi AI atau harga.
- Frontend WhatsApp utama menggunakan `601169409606`; beberapa aliran invoice boleh mengambil nombor admin daripada payment settings.
- Gunakan Bahasa Melayu untuk komunikasi pengguna dan teruskan dengan andaian munasabah jika repo sudah menjawab soalan tersebut.
