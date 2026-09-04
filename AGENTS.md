# AGENTS.md

## Commands

- Fresh setup dari root repo: `composer run setup`; skrip ini install dependency PHP, cipta `.env` jika belum ada, jana key, jalankan `php artisan migrate --force`, `npm install --ignore-scripts`, kemudian `npm run build`.
- Local stack: `composer run dev`; ia menjalankan Laravel server, queue listener, Pail logs dan Vite melalui `concurrently`.
- Frontend check: `npm run typecheck`; release check: `npm run build` (`tsc` kemudian `vite build`). Tiada skrip `npm run lint`.
- Test penuh: `composer test` (clear config kemudian `php artisan test`) atau `php artisan test` secara terus.
- Test fokus: `php artisan test tests/Feature/AdminOrderIndexTest.php` atau `php artisan test tests/Feature/AdminLoginBrandingTest.php`.
- Format PHP dengan `vendor/bin/pint`; sebelum commit wajib jalankan `npm run build` dari root repo, kemudian commit dan push dengan mesej Bahasa Melayu yang ringkas.

## Structure

- Stack utama ialah Laravel 13 dengan PHP `^8.3`, Inertia React `^2`, React `^19`, TypeScript, Vite `^8` dan Tailwind CSS `^4`.
- Ini satu aplikasi Laravel, bukan monorepo. `routes/web.php` memegang route public, member dan admin; controller dipisahkan kepada namespace umum, `Admin` dan `Member`.
- `resources/js/app.tsx` ialah entry Inertia React dan resolve page melalui `import.meta.glob('./Pages/**/*.tsx')`; page berada di `resources/js/Pages`.
- Layout dikongsi melalui `resources/js/Components/Layouts/{FrontendLayout,MemberLayout,AdminLayout}.tsx`; alias TypeScript ditetapkan dalam `tsconfig.json` (`@/*`, `@components/*`, `@pages/*`, `@hooks/*`, `@lib/*`, `@types/*`).
- `vite.config.js` hanya membina `resources/css/app.css` dan `resources/js/app.tsx`; Tailwind 4 ditetapkan dalam `resources/css/app.css` melalui `@theme`, bukan `tailwind.config.js`.
- Route admin menggunakan prefix `admin` dan middleware `auth` + `admin`; route member menggunakan middleware `member`; route public utama berada di bawah `under_construction`.
- Aliran page menggunakan controller Laravel + `Inertia::render()` dan props, bukan API JSON berasingan; pengecualian utama ialah endpoint `/api/designs`.

## Tests And Runtime

- `phpunit.xml` mengatasi `.env.testing`: test menggunakan SQLite `:memory:`, cache/session `array`, queue `sync` dan mail `array`. Test tidak mengesahkan schema MySQL dalam `.env` runtime.
- `.env.example` bermula dengan SQLite, tetapi `.env` tempatan/deployment boleh menggunakan MySQL. Selepas migration baharu, jalankan `php artisan migrate` pada database sebenar; `php artisan migrate:status` membantu mengesan schema tertinggal.
- Jika upload order gagal dengan `Unknown column` untuk `order_items.admin_source_path`, `admin_source_paths`, `customer_preview_path` atau `customer_preview_paths`, jalankan migration `2026_08_25_000001` dan `2026_08_25_000002` pada database runtime sebelum mengubah controller. Test upload menggunakan `Storage::fake('local')`.
- `public/build` ialah artifact yang tracked. `npm run build` boleh menukar manifest dan banyak asset hashed; sertakan semua output build yang dijana apabila perubahan frontend perlu dideploy.
- Private source/preview order disimpan pada disk `local` di `storage/app/private`; jangan tukar kepada public URL tanpa menyemak route kawalan akses.

## Domain Constraints

- Logik harga gunakan `app/Services/StickerPricingService.php`; minimum ialah 3 helai A3 tanpa design dan 1 helai A3 dengan design. Jangan duplikasi kiraan harga dalam controller.
- Logik pos gunakan `app/Services/ShippingService.php`: percuma apabila subtotal sekurang-kurangnya RM150 atau `shipping_free` true, selain itu RM7 Semenanjung dan RM12 Sabah/Sarawak.
- `StickerSize` mesti mengekalkan cast `float` untuk `width_cm` dan `height_cm`; nilai DECIMAL MySQL boleh sampai ke frontend sebagai string jika cast dibuang.
- Tracking customer hanya boleh keluar apabila order `completed`, melalui `Order::customerTrackingNo()` dan `Invoice::customerTrackingNo()`. Menyimpan tracking invoice tidak boleh sendiri melengkapkan order; notifikasi tracking dihantar ketika transisi ke `completed`.
- Galeri home ialah data statik `resources/js/data/showcase.ts` dengan imej `public/images/showcase/sticker-01.webp` hingga `sticker-35.webp`, bukan data design database.
- Seeder tempatan mencipta admin `admin@sticker` dengan kata laluan `123`; gunakan hanya untuk local database dan jangan masukkan credential sebenar dalam repo.

## Integrations And Deployment

- Credential integrasi hanya datang daripada `.env.example`/`.env`: Google OAuth/Analytics/Contacts (`GOOGLE_*`), Meta Ads (`META_*`), SumoPod (`SUMOPOD_*`) dan J&T (`JNT_*`). Jangan letakkan secret dalam source atau `AGENTS.md`.
- MCP app berada di `app/Mcp`; definisi HTTP dan local transport berada di `routes/ai.php` dengan handle `sticker-store` dan `agno`. Semak fail ini sebelum mengubah integrasi AI atau harga.
- Fallback WhatsApp ialah `601169409606`; sesetengah flow menggunakan `PaymentSetting.admin_phone` sebagai override.
- `.vscode/sftp.json` menetapkan FTP dengan `uploadOnSave: false`; `git push` sahaja tidak bermaksud deployment selesai. Jalankan migration dan build pada environment deployment mengikut proses sebenar.
- `README.md` ialah boilerplate Laravel dan `DESIGN_SYSTEM_INERTIA_REACT.md` mengandungi rujukan sejarah/target; utamakan manifest, config, routes, tests dan code semasa jika bercanggah.

## Repository Workflow

- Dalam checkout ini, `.git/hooks/post-commit` menjalankan `graphify` selepas commit dan boleh melebihi timeout terminal; semak `git status` dan `git log` sebelum mengulangi commit.
- Jangan commit `.env`, `vendor`, `node_modules`, `graphify-out` atau log; aturan ini datang daripada `.gitignore`.
