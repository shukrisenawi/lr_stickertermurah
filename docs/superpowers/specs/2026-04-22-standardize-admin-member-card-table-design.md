# Spec: Seragamkan Card dan Table Admin + Member

Tarikh: 2026-04-22
Status: Dicadangkan

## Objektif

Menyeragamkan gaya visual komponen `card` dan `table` pada semua halaman `admin` dan `member` supaya selari dengan bahasa reka bentuk yang sudah berjaya digunakan pada `admin/dashboard`.

Skop ini menumpukan pada komponen dan struktur paparan, bukan perubahan logik perniagaan atau aliran data.

## Masalah Semasa

Bahagian `admin/dashboard` sudah menggunakan sistem visual yang lebih konsisten melalui:

- `admin-flat-card`
- `admin-table`
- `admin-title-block`
- `admin-section-title`
- butang utama dan sekunder yang seragam

Namun halaman lain masih bercampur antara gaya lama dan gaya baru:

- sesetengah halaman menggunakan `bg-white rounded-2xl ring-1 ring-slate-200`
- sesetengah jadual dibina dengan class table manual yang tidak sama
- spacing, header table, empty state, dan action button tidak konsisten
- halaman member sudah lebih kemas tetapi belum benar-benar sepadan dengan standard dashboard

Akibatnya, panel pentadbiran dan ahli nampak seperti berasal daripada beberapa sistem UI yang berbeza.

## Skop

Skop termasuk:

- semua halaman `admin` yang menggunakan card atau table
- semua halaman `member` yang menggunakan card atau table
- penyeragaman visual pada:
  - wrapper card utama
  - header seksyen
  - table wrapper
  - table header/body/empty state
  - action row dan button dalam table
  - form card utama untuk create/edit/show yang perlu rasa sekeluarga dengan dashboard

Skop tidak termasuk:

- halaman frontend awam seperti landing page utama
- rombakan besar layout global
- perubahan route, controller, query, atau business rule
- perubahan gaya cetakan invoice yang boleh menjejaskan output print

## Pendekatan Dipilih

Pendekatan yang dipilih ialah:

1. Gunakan `admin/dashboard` sebagai sumber rujukan visual tunggal untuk halaman admin.
2. Luaskan pattern yang sama ke halaman member dengan padanan kelas `frontend-flat-card` dan jadual yang setara.
3. Utamakan reusable utility/class sedia ada dalam `resources/css/app.css` sebelum menulis class inline baharu.
4. Hanya kekalkan pengecualian visual apabila halaman memang mempunyai keperluan khas seperti `invoice show`.

Pendekatan ini dipilih kerana ia memberikan hasil paling konsisten dengan risiko paling rendah terhadap logik sistem.

## Halaman Sasaran

### Admin

- `orders/index`
- `orders/show`
- `customers/index`
- `categories/index`
- `categories/create`
- `categories/edit`
- `sizes/index`
- `sizes/create`
- `sizes/edit`
- `designs/index`
- `designs/create`
- `designs/edit`
- `invoices/create`
- `invoices/manual`
- `contacts/google`
- `contacts/extract`
- `jnt/index`
- `profile/edit`
- `profile/password`

### Member

- `member/dashboard`
- `member/orders/index`
- `member/orders/show`
- `member/invoices/show`

## Reka Bentuk Komponen

### Card

Semua card utama akan diseragamkan kepada ciri berikut:

- latar putih
- border halus `slate-200`
- shadow ringan seperti dashboard
- radius besar yang konsisten
- padding dalaman yang stabil antara halaman

Corak penggunaan:

- card utama halaman: `admin-flat-card` atau `frontend-flat-card`
- card statistik kecil: ikut corak `admin-kpi-card` atau versi ringan yang setara
- card form: satu wrapper utama, bukan campuran beberapa box berbeza tanpa hierarki

### Table

Semua table akan diseragamkan kepada corak berikut:

- wrapper card tunggal
- `overflow-x-auto` yang konsisten
- header table dengan latar `slate-50`
- teks kepala table uppercase kecil dan tracking yang sama
- row hover lembut
- border row yang konsisten
- padding sel yang seragam
- empty state yang lebih terancang dan senada dengan dashboard

Untuk halaman member, jadual akan menyamai rasa visual dashboard tanpa memaksa penggunaan class `admin-*` secara literal jika shell yang digunakan ialah `frontend`.

### Section Header

Semua halaman yang relevan akan menggunakan struktur tajuk yang sama:

- accent bar menegak
- tajuk utama tebal
- copy penerangan ringkas
- CTA di sebelah kanan jika sesuai

Ini akan menggantikan tajuk lama yang masih menggunakan typography dan casing yang tidak seragam.

## Strategi Pelaksanaan

1. Tambah atau kemaskan utility class dalam `resources/css/app.css`.
2. Refactor halaman index yang heavy-table terlebih dahulu kerana impaknya paling jelas.
3. Refactor halaman form/create/edit supaya wrapper card dan spacing sama keluarga.
4. Kemaskan halaman show/detail yang masih gunakan card lama.
5. Semak semula member pages untuk pastikan table dan card tidak tertinggal.

## Risiko dan Mitigasi

### Risiko 1: Halaman khas kehilangan identiti fungsi

Sesetengah halaman seperti `invoice show` atau `jnt` mempunyai struktur khas.

Mitigasi:

- kekalkan layout khas jika ia menyokong fungsi
- hanya seragamkan card/table tone, bukan paksa semua jadi sama 100%

### Risiko 2: Terlalu banyak class inline bercampur dengan utility baharu

Mitigasi:

- pindahkan pattern berulang ke `app.css`
- kurangkan pengulangan class panjang pada view yang sama

### Risiko 3: Regresi paparan pada mobile

Mitigasi:

- kekalkan wrapper `overflow-x-auto`
- elakkan hard-coded spacing yang terlalu besar
- semak jadual utama selepas refactor

## Ujian dan Verifikasi

Sebelum kerja dianggap siap:

- `npm run build` mesti lulus
- semak paparan table utama admin:
  - orders
  - customers
  - categories
  - sizes
  - invoices
- semak sekurang-kurangnya satu halaman form create/edit bagi admin
- semak halaman member orders dan invoice
- pastikan empty state dan pagination masih berfungsi

## Keputusan Reka Bentuk Muktamad

- Fokus sesi implementasi seterusnya ialah `admin + member`
- Dashboard admin menjadi standard visual utama
- Frontend awam tidak disentuh dalam skop ini
- Perubahan tertumpu pada komponen card dan table, bukan logik aplikasi
