# Reka Bentuk Sistem: StickerTermurah Inertia + React

> Dokumen reka bentuk sistem keseluruhan untuk migrasi dari Laravel Blade + AlpineJS ke **Laravel + Inertia.js + React + Tailwind CSS v4**.

---

## 1. Executive Summary

### 1.1 Matlamat
- Memodenkan frontend dengan React untuk pengalaman pengguna (UX) yang lebih baik.
- Menyelaraskan logik UI antara Frontend, Member, dan Admin dalam satu codebase JavaScript.
- Memanfaatkan **Inertia.js** supaya tidak perlu membina API terpisah (SPA tanpa API).
- Mengekalkan semua keupayaan backend Laravel sedia ada: Auth, Order, Invoice, J&T, Google Contacts, dsb.

### 1.2 Stack Teknologi Baru

| Lapisan | Teknologi | Versi |
|---------|-----------|-------|
| Backend | Laravel | 13.x |
| Bridge | Inertia.js | ^2.x |
| Frontend | React | ^19.x |
| Styling | Tailwind CSS | ^4.x |
| Build | Vite | ^8.x |
| Icons | Lucide React | ^0.x |
| Form | React Hook Form + Zod | latest |
| State | React Context + useState/useReducer | built-in |

---

## 2. Analisis Sistem Sedia Ada

### 2.1 Struktur Semasa (Blade + AlpineJS)

```
resources/views/
├── layouts/
│   ├── frontend.blade.php      ← Layout umum (header, nav, footer)
│   └── admin.blade.php         ← Layout admin (sidebar, header)
├── frontend/
│   ├── home.blade.php          ← Landing page
│   ├── order-form.blade.php    ← Borang tempahan sticker
│   ├── order-thank-you.blade.php
│   └── lookup-order.blade.php  ← Semakan order
├── member/
│   ├── auth/login.blade.php
│   ├── auth/register.blade.php
│   ├── dashboard.blade.php
│   ├── orders/index.blade.php
│   ├── orders/show.blade.php
│   └── invoices/show.blade.php
└── admin/
    ├── auth/login.blade.php
    ├── dashboard.blade.php
    ├── orders/index.blade.php, show.blade.php
    ├── customers/index.blade.php
    ├── invoices/create.blade.php, show.blade.php, manual.blade.php
    ├── categories/index.blade.php, create.blade.php, edit.blade.php
    ├── designs/index.blade.php, create.blade.php, edit.blade.php
    ├── sizes/index.blade.php, create.blade.php, edit.blade.php
    ├── contacts/google.blade.php, extract.blade.php
    ├── jnt/index.blade.php
    └── profile/edit.blade.php, password.blade.php
```

### 2.2 Domain & Modul

| Domain | Peranan | Halaman Utama |
|--------|---------|---------------|
| **Public** | Pelawat tanpa login | Home, Semak Order |
| **Member** | Ahli berdaftar | Login, Register, Dashboard, Order Saya, Invois |
| **Admin** | Pentadbir | Dashboard, Orders, Customers, Kategori, Design, Saiz, Invois, J&T, Contacts, Profil |

### 2.3 Integrasi Penting
- **Google OAuth** (`Socialite`) — Login ahli
- **J&T Express API** — Penghasilan waybill & tracking
- **Google Contacts API** — Pengurusan kenalan
- **OpenAI API** — Ciri AI (jika ada)

---

## 3. Arsitektur Target: Inertia + React

### 3.1 Konsep Asas Inertia

Inertia.js bertindak sebagai "penghubung" antara Laravel dan React:
- **Laravel** mengendalikan routing, controller, middleware, dan business logic.
- **React** mengendalikan rendering UI di browser.
- Tiada API JSON terpisah — controller Laravel terus menghantar `Inertia::render()` dengan props.

```
[Browser] ←→ [Inertia.js Router] ←→ [Laravel Controllers]
     ↑               ↓                        ↓
   React        XHR Requests            Models/DB
  Components    (page props)           Business Logic
```

### 3.2 Aliran Data (Data Flow)

```
1. Pengguna klik pautan / admin/orders
2. Inertia.js menghantar XHR ke Laravel
3. Laravel route → Admin\OrderController@index
4. Controller query DB → dapatkan OrderCollection
5. Controller return Inertia::render('Admin/Orders/Index', [
       'orders' => OrderResource::collection($orders),
       'filters' => $filters,
   ])
6. Inertia.js menerima JSON response
7. React memasang komponen `Pages/Admin/Orders/Index`
8. Props `orders` dan `filters` dihantar sebagai prop kepada page component
```

---

## 4. Struktur Direktori Baru

```
laravel-stickertermurah/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   ├── FrontendController.php         ← Perlu diubah ke Inertia::render()
│   │   │   ├── OrderController.php
│   │   │   ├── Member/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── InvoiceController.php
│   │   │   │   └── OrderController.php
│   │   │   └── Admin/
│   │   │       ├── AuthController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── ContactExtractionController.php
│   │   │       ├── CustomerController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── GoogleContactController.php
│   │   │       ├── InvoiceController.php
│   │   │       ├── JntController.php
│   │   │       ├── OrderController.php
│   │   │       ├── ProfileController.php
│   │   │       ├── StickerDesignController.php
│   │   │       └── StickerSizeController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php            ← Kekal
│   │   │   └── HandleInertiaRequests.php      ← BARU: Shared props
│   │   └── Resources/
│   │       ├── CategoryResource.php           ← BARU: API Resource
│   │       ├── CustomerResource.php
│   │       ├── InvoiceResource.php
│   │       ├── OrderResource.php
│   │       ├── StickerDesignResource.php
│   │       ├── StickerSizeResource.php
│   │       └── UserResource.php
│   ├── Models/
│   └── Providers/
│       └── AppServiceProvider.php
│
├── resources/
│   ├── js/
│   │   ├── app.tsx                            ← Entry point Inertia + React
│   │   ├── bootstrap.ts                       ← Axios config
│   │   ├── Components/
│   │   │   ├── UI/                            ← Komponen UI asas (shared)
│   │   │   │   ├── Button.tsx
│   │   │   │   ├── Card.tsx
│   │   │   │   ├── Input.tsx
│   │   │   │   ├── Select.tsx
│   │   │   │   ├── Badge.tsx
│   │   │   │   ├── Modal.tsx
│   │   │   │   ├── Table.tsx
│   │   │   │   ├── Pagination.tsx
│   │   │   │   ├── EmptyState.tsx
│   │   │   │   └── LoadingSpinner.tsx
│   │   │   ├── Layouts/
│   │   │   │   ├── FrontendLayout.tsx         ← Ganti frontend.blade.php
│   │   │   │   ├── MemberLayout.tsx           ← Layout member area
│   │   │   │   └── AdminLayout.tsx            ← Ganti admin.blade.php
│   │   │   └── Shared/
│   │   │       ├── FlashMessages.tsx          ← Session flash alerts
│   │   │       ├── Navbar.tsx                 ← Frontend navbar
│   │   │       ├── MobileMenu.tsx
│   │   │       ├── AdminSidebar.tsx           ← Admin sidebar
│   │   │       ├── AdminHeader.tsx
│   │   │       └── Footer.tsx                 ← Frontend footer
│   │   ├── Pages/
│   │   │   ├── Public/
│   │   │   │   ├── Home.tsx                   ← Ganti home.blade.php
│   │   │   │   ├── OrderForm.tsx              ← Ganti order-form.blade.php
│   │   │   │   ├── OrderThankYou.tsx
│   │   │   │   └── LookupOrder.tsx            ← Ganti lookup-order.blade.php
│   │   │   ├── Auth/
│   │   │   │   ├── MemberLogin.tsx            ← Ganti member/auth/login.blade.php
│   │   │   │   ├── MemberRegister.tsx         ← Ganti member/auth/register.blade.php
│   │   │   │   └── AdminLogin.tsx             ← Ganti admin/auth/login.blade.php
│   │   │   ├── Member/
│   │   │   │   ├── Dashboard.tsx              ← Ganti member/dashboard.blade.php
│   │   │   │   ├── Orders/
│   │   │   │   │   ├── Index.tsx              ← Ganti member/orders/index.blade.php
│   │   │   │   │   └── Show.tsx               ← Ganti member/orders/show.blade.php
│   │   │   │   └── Invoices/
│   │   │   │       └── Show.tsx
│   │   │   └── Admin/
│   │   │       ├── Dashboard.tsx              ← Ganti admin/dashboard.blade.php
│   │   │       ├── Orders/
│   │   │       │   ├── Index.tsx
│   │   │       │   └── Show.tsx
│   │   │       ├── Customers/
│   │   │       │   └── Index.tsx
│   │   │       ├── Categories/
│   │   │       │   ├── Index.tsx
│   │   │       │   ├── Create.tsx
│   │   │       │   └── Edit.tsx
│   │   │       ├── Designs/
│   │   │       │   ├── Index.tsx
│   │   │       │   ├── Create.tsx
│   │   │       │   └── Edit.tsx
│   │   │       ├── Sizes/
│   │   │       │   ├── Index.tsx
│   │   │       │   ├── Create.tsx
│   │   │       │   └── Edit.tsx
│   │   │       ├── Invoices/
│   │   │       │   ├── Create.tsx
│   │   │       │   ├── Show.tsx
│   │   │       │   └── ManualCreate.tsx
│   │   │       ├── Contacts/
│   │   │       │   ├── GoogleContacts.tsx
│   │   │       │   └── Extract.tsx
│   │   │       ├── Jnt/
│   │   │       │   └── Index.tsx
│   │   │       └── Profile/
│   │   │           ├── Edit.tsx
│   │   │           └── Password.tsx
│   │   ├── hooks/
│   │   │   ├── useAuth.ts                     ← Hook untuk auth state
│   │   │   ├── useForm.ts                     ← Wrapper React Hook Form
│   │   │   └── useFlash.ts                    ← Hook untuk flash messages
│   │   ├── types/
│   │   │   ├── index.ts                       ← Global types
│   │   │   ├── models.ts                    ← Model interfaces
│   │   │   └── inertia.d.ts                   ← Inertia type extensions
│   │   └── lib/
│   │       ├── utils.ts                       ← cn() helper (clsx + tailwind-merge)
│   │       ├── constants.ts                   ← App constants
│   │       └── formatters.ts                  ← Date/currency formatters
│   └── css/
│       └── app.css                            ← Kekal (Tailwind v4)
│
├── routes/
│   ├── web.php                                ← Kekal (disesuaikan)
│   └── auth.php                               ← Jika perlu
│
├── composer.json                              ← Tambah inertiajs/inertia-laravel
├── package.json                               ← Tambah @inertiajs/react, react, react-dom
├── vite.config.js                             ← Update input ke app.tsx
└── tsconfig.json                              ← BARU
```

---

## 5. Komponen & Layout (React)

### 5.1 Hierarki Layout

```
<App>                              ← Inertia root
  ├── <FrontendLayout>             ← Route: home, order-form, lookup
  │     ├── <Navbar />
  │     ├── <main>{children}</main>
  │     └── <Footer />
  │
  ├── <MemberLayout>               ← Route: member.dashboard, member.orders.*
  │     ├── <Navbar />           ← Member navbar (berbeza sedikit)
  │     ├── <main>{children}</main>
  │     └── <Footer />
  │
  └── <AdminLayout>              ← Route: admin.*
        ├── <AdminSidebar />
        ├── <AdminHeader />
        └── <main>{children}</main>
```

### 5.2 Perincian Layout

#### `FrontendLayout.tsx`
- **Gunakan untuk**: Semua halaman umum (public)
- **Komponen**: Navbar (sticky, glassmorphism), MobileMenu, Footer
- **Shared Data**: `auth.user` (jika login), `flash.success`, `flash.error`

#### `MemberLayout.tsx`
- **Gunakan untuk**: Member area (dashboard, orders, invoices)
- **Komponen**: Navbar (dengan avatar & dropdown), Footer
- **Shared Data**: `auth.user`, `auth.customerAddresses`

#### `AdminLayout.tsx`
- **Gunakan untuk**: Semua halaman admin
- **Komponen**: Sidebar (collapsible), Header (search, notifications, profile dropdown)
- **Shared Data**: `auth.user` (admin only), `flash.success`, `flash.error`

### 5.3 Komponen UI Asas (Shared)

| Komponen | Tujuan |
|----------|--------|
| `Button` | Variants: primary, secondary, danger, ghost, link |
| `Card` | Container dengan border, shadow, rounded |
| `Input` | Text input dengan label, error, helper text |
| `Select` | Dropdown select |
| `Badge` | Status badge (warna mengikut status) |
| `Modal` | Dialog overlay untuk konfirmasi / form |
| `Table` | Data table dengan sorting, pagination hooks |
| `Pagination` | Page numbers + prev/next |
| `EmptyState` | Ilustrasi + teks untuk keadaan kosong |
| `LoadingSpinner` | Loading indicator |

---

## 6. State Management

### 6.1 Filosofi State

Dengan Inertia, sebahagian besar data adalah **server-driven** (props daripada Laravel). Hanya state lokal yang diurus dengan React hooks.

```
Server State (Laravel → Inertia → Props)
├── Orders list
├── User profile
├── Order details
└── Dashboard metrics

Client State (React useState/useReducer)
├── UI state (modal open/close, sidebar collapsed)
├── Form state (input values, validation errors)
├── Filter state (search, sort, tab aktif)
└── Pagination state (current page)
```

### 6.2 Context Providers

```tsx
// resources/js/app.tsx
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });
    return pages[`./Pages/${name}.tsx`];
  },
  setup({ el, App, props }) {
    createRoot(el).render(
      <FlashProvider>          {/* Flash messages dari session */}
        <AuthProvider>           {/* Auth state global */}
          <App {...props} />
        </AuthProvider>
      </FlashProvider>
    );
  },
});
```

### 6.3 Auth Hook (`useAuth`)

```tsx
// resources/js/hooks/useAuth.ts
import { usePage } from '@inertiajs/react';

export function useAuth() {
  const { auth } = usePage().props;
  return {
    user: auth.user,
    isAdmin: auth.user?.is_admin ?? false,
    isMember: !!auth.user && !auth.user.is_admin,
    isGuest: !auth.user,
  };
}
```

---

## 7. Routing & Navigation

### 7.1 Laravel Routes (Kekal Hampir Sama)

```php
// routes/web.php — Kekal struktur yang sama!
// Hanya tukar return view() kepada Inertia::render()

Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/semak-order', [FrontendController::class, 'lookupForm'])->name('orders.lookup-form');

Route::prefix('ahli')->name('member.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/daftar', [MemberAuthController::class, 'showRegister'])->name('register');
        Route::post('/daftar', [MemberAuthController::class, 'register'])->name('register.store');
        Route::get('/login', [MemberAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [MemberAuthController::class, 'login'])->name('login.attempt');
    });

    Route::post('/logout', [MemberAuthController::class, 'logout'])->middleware('auth')->name('logout');
    Route::get('/dashboard', MemberDashboardController::class)->middleware('auth')->name('dashboard');
    Route::get('/orders', [MemberOrderController::class, 'index'])->middleware('auth')->name('orders.index');
    Route::get('/orders/{order}', [MemberOrderController::class, 'show'])->middleware('auth')->name('orders.show');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    // ...semua route admin lain
});
```

### 7.2 React Navigation

Gunakan `<Link>` daripada `@inertiajs/react` untuk semua navigasi dalaman:

```tsx
import { Link } from '@inertiajs/react';

<Link href={route('member.orders.index')} className="...">
  Order Saya
</Link>

<Link href={route('admin.dashboard')} className="...">
  Dashboard Admin
</Link>
```

### 7.3 Definisi Route (Type-Safe)

```php
// routes/web.php + ziggy
// Gunakan `tightenco/ziggy` untuk generate route definitions
// Selepas install: php artisan ziggy:generate
```

```ts
// resources/js/types/ziggy.d.ts (auto-generated)
// Supaya route('member.orders.index') adalah type-safe
```

---

## 8. Backend Adaptations

### 8.1 Middleware: HandleInertiaRequests

```php
<?php
// app/Http/Middleware/HandleInertiaRequests.php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'is_admin' => $request->user()->is_admin,
                    'avatar_url' => $request->user()->avatar_path
                        ? asset('storage/' . $request->user()->avatar_path)
                        : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
            ],
        ];
    }
}
```

### 8.2 Root View (`app.blade.php`)

```blade
{{-- resources/views/app.blade.php --}}
<!DOCTYPE html>
<html lang="ms" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-baru.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @routes
    @viteReactRefresh
    @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="min-h-full bg-white text-slate-900 antialiased">
    @inertia
</body>
</html>
```

### 8.3 Controller Refactor Contoh

```php
<?php
// app/Http/Controllers/FrontendController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\StickerDesign;
use Inertia\Inertia;

class FrontendController extends Controller
{
    public function home()
    {
        return Inertia::render('Public/Home', [
            'categories' => Category::with('designs')->get(),
            'designs' => StickerDesign::featured()->limit(8)->get(),
            'testimonials' => $this->getTestimonials(), // jika ada
        ]);
    }

    public function orderForm(?Order $repeatOrder = null)
    {
        return Inertia::render('Public/OrderForm', [
            'designs' => StickerDesign::all(),
            'sizes' => StickerSize::all(),
            'priceTiers' => StickerPriceTier::all(),
            'repeatOrder' => $repeatOrder ? new OrderResource($repeatOrder) : null,
        ]);
    }

    public function lookupForm()
    {
        return Inertia::render('Public/LookupOrder');
    }
}
```

### 8.4 API Resources (Untuk Type Safety)

```php
<?php
// app/Http/Resources/OrderResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_no' => $this->order_no,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'material' => $this->material,
            'status' => $this->status,
            'tracking_no' => $this->tracking_no,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'created_at' => $this->created_at->toISOString(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
```

---

## 9. Rancangan Implementasi (Migration Plan)

### Fasa 1: Persediaan Infrastruktur (Week 1)

| Langkah | Tindakan |
|---------|----------|
| 1.1 | `composer require inertiajs/inertia-laravel` |
| 1.2 | `php artisan inertia:middleware` → Update `bootstrap/app.php` |
| 1.3 | Install React deps: `npm install @inertiajs/react react react-dom` |
| 1.4 | Install dev deps: `npm install -D @types/react @types/react-dom typescript` |
| 1.5 | Install util deps: `npm install lucide-react clsx tailwind-merge zod react-hook-form @hookform/resolvers` |
| 1.6 | Buat `tsconfig.json` dan konfigurasi TypeScript |
| 1.7 | Update `vite.config.js` untuk support React & TypeScript |
| 1.8 | Buat `resources/views/app.blade.php` (root view Inertia) |
| 1.9 | Buat `resources/js/app.tsx` (entry point) |
| 1.10 | Buat `HandleInertiaRequests.php` middleware |

### Fasa 2: Layout & Shared Components (Week 1-2)

| Langkah | Tindakan |
|---------|----------|
| 2.1 | Pindah `frontend.blade.php` → `FrontendLayout.tsx` |
| 2.2 | Pindah `admin.blade.php` → `AdminLayout.tsx` |
| 2.3 | Buat `MemberLayout.tsx` |
| 2.4 | Buat komponen UI asas: Button, Card, Input, Badge, Modal, Table |
| 2.5 | Buat `Navbar.tsx`, `Footer.tsx`, `AdminSidebar.tsx` |
| 2.6 | Buat `FlashMessages.tsx` untuk session alerts |

### Fasa 3: Public Pages (Week 2)

| Langkah | Tindakan |
|---------|----------|
| 3.1 | Refactor `FrontendController@home` → `Inertia::render('Public/Home')` |
| 3.2 | Pindah `home.blade.php` → `Pages/Public/Home.tsx` |
| 3.3 | Pindah `order-form.blade.php` → `Pages/Public/OrderForm.tsx` |
| 3.4 | Pindah `order-thank-you.blade.php` → `Pages/Public/OrderThankYou.tsx` |
| 3.5 | Pindah `lookup-order.blade.php` → `Pages/Public/LookupOrder.tsx` |
| 3.6 | Uji semua public pages berfungsi dengan Inertia |

### Fasa 4: Auth Pages (Week 2-3)

| Langkah | Tindakan |
|---------|----------|
| 4.1 | Pindah `member/auth/login.blade.php` → `Pages/Auth/MemberLogin.tsx` |
| 4.2 | Pindah `member/auth/register.blade.php` → `Pages/Auth/MemberRegister.tsx` |
| 4.3 | Pindah `admin/auth/login.blade.php` → `Pages/Auth/AdminLogin.tsx` |
| 4.4 | Pastikan Google OAuth redirect berfungsi dengan Inertia |

### Fasa 5: Member Area (Week 3)

| Langkah | Tindakan |
|---------|----------|
| 5.1 | Pindah `member/dashboard.blade.php` → `Pages/Member/Dashboard.tsx` |
| 5.2 | Pindah `member/orders/index.blade.php` → `Pages/Member/Orders/Index.tsx` |
| 5.3 | Pindah `member/orders/show.blade.php` → `Pages/Member/Orders/Show.tsx` |
| 5.4 | Pindah `member/invoices/show.blade.php` → `Pages/Member/Invoices/Show.tsx` |
| 5.5 | Uji semua member routes |

### Fasa 6: Admin Area (Week 3-4)

| Langkah | Tindakan |
|---------|----------|
| 6.1 | Pindah `admin/dashboard.blade.php` → `Pages/Admin/Dashboard.tsx` |
| 6.2 | Pindah `admin/orders/*.blade.php` → `Pages/Admin/Orders/*.tsx` |
| 6.3 | Pindah `admin/customers/*.blade.php` → `Pages/Admin/Customers/*.tsx` |
| 6.4 | Pindah `admin/categories/*.blade.php` → `Pages/Admin/Categories/*.tsx` |
| 6.5 | Pindah `admin/designs/*.blade.php` → `Pages/Admin/Designs/*.tsx` |
| 6.6 | Pindah `admin/sizes/*.blade.php` → `Pages/Admin/Sizes/*.tsx` |
| 6.7 | Pindah `admin/invoices/*.blade.php` → `Pages/Admin/Invoices/*.tsx` |
| 6.8 | Pindah `admin/contacts/*.blade.php` → `Pages/Admin/Contacts/*.tsx` |
| 6.9 | Pindah `admin/jnt/*.blade.php` → `Pages/Admin/Jnt/*.tsx` |
| 6.10 | Pindah `admin/profile/*.blade.php` → `Pages/Admin/Profile/*.tsx` |

### Fasa 7: Polish & Optimization (Week 4)

| Langkah | Tindakan |
|---------|----------|
| 7.1 | Implementasi `useForm` hook untuk seragamkan form handling |
| 7.2 | Tambah loading states pada semua async actions |
| 7.3 | Optimize images dengan React lazy loading |
| 7.4 | Tambah error boundaries |
| 7.5 | Uji responsiveness pada semua pages |
| 7.6 | Jalankan `npm run build` → pastikan tiada error |
| 7.7 | Jalankan `php artisan test` → pastikan semua test lulus |

### Fasa 8: Cleanup (Week 4)

| Langkah | Tindakan |
|---------|----------|
| 8.1 | Buang semua fail `.blade.php` (backup dahulu) |
| 8.2 | Buang AlpineJS dari dependencies |
| 8.3 | Update `AGENTS.md` dengan stack baru |
| 8.4 | Commit & push ke repository |

---

## 10. Dependencies (package.json Target)

```json
{
  "private": true,
  "type": "module",
  "scripts": {
    "build": "tsc && vite build",
    "dev": "vite",
    "lint": "eslint . --ext ts,tsx --report-unused-disable-directives --max-warnings 0",
    "typecheck": "tsc --noEmit"
  },
  "dependencies": {
    "@inertiajs/react": "^2.0.0",
    "@hookform/resolvers": "^3.0.0",
    "axios": "^1.7.0",
    "clsx": "^2.1.0",
    "lucide-react": "^0.460.0",
    "react": "^19.0.0",
    "react-dom": "^19.0.0",
    "react-hook-form": "^7.54.0",
    "tailwind-merge": "^2.6.0",
    "ziggy-js": "^2.4.0",
    "zod": "^3.24.0"
  },
  "devDependencies": {
    "@tailwindcss/vite": "^4.0.0",
    "@types/react": "^19.0.0",
    "@types/react-dom": "^19.0.0",
    "@vitejs/plugin-react": "^4.3.0",
    "autoprefixer": "^10.4.0",
    "concurrently": "^9.0.0",
    "laravel-vite-plugin": "^3.0.0",
    "tailwindcss": "^4.0.0",
    "typescript": "^5.7.0",
    "vite": "^8.0.0"
  }
}
```

---

## 11. TypeScript Configuration

### 11.1 `tsconfig.json`

```json
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["./resources/js/*"],
      "@components/*": ["./resources/js/Components/*"],
      "@pages/*": ["./resources/js/Pages/*"],
      "@hooks/*": ["./resources/js/hooks/*"],
      "@lib/*": ["./resources/js/lib/*"],
      "@types/*": ["./resources/js/types/*"]
    }
  },
  "include": ["resources/js/**/*"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

### 11.2 Model Types (`resources/js/types/models.ts`)

```typescript
export interface User {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  avatar_url: string | null;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  designs?: StickerDesign[];
}

export interface StickerDesign {
  id: number;
  name: string;
  category_id: number;
  image_url: string;
  // ...
}

export interface StickerSize {
  id: number;
  name: string;
  width_mm: number;
  height_mm: number;
}

export interface StickerPriceTier {
  id: number;
  sticker_size_id: number;
  min_qty: number;
  max_qty: number | null;
  price_per_unit: number;
}

export interface OrderItem {
  id: number;
  order_id: number;
  sticker_design_id: number;
  sticker_size_id: number;
  quantity: number;
  unit_price: number;
  subtotal: number;
  design?: StickerDesign;
  size?: StickerSize;
}

export interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  material: string;
  status: 'pending' | 'processing' | 'shipped' | 'completed' | 'cancelled';
  tracking_no: string | null;
  subtotal: number;
  total: number;
  created_at: string;
  items?: OrderItem[];
  invoice?: Invoice;
  user?: User;
}

export interface Invoice {
  id: number;
  order_id: number;
  invoice_no: string;
  amount: number;
  status: string;
  created_at: string;
}

export interface CustomerAddress {
  id: number;
  user_id: number;
  label: string;
  address: string;
  phone: string;
  is_default: boolean;
}
```

---

## 12. Form Handling Pattern

### 12.1 Wrapper Form Hook

```tsx
// resources/js/hooks/useInertiaForm.ts
import { useForm as useInertiaForm } from '@inertiajs/react';
import { useForm as useReactHookForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { ZodSchema } from 'zod';

// Hybrid approach: React Hook Form untuk client validation,
// Inertia useForm untuk submission
export function useHybridForm<T extends Record<string, any>>(
  schema: ZodSchema<T>,
  inertiaInitialData: T,
  submitRoute: string,
  method: 'post' | 'put' | 'patch' = 'post'
) {
  const inertiaForm = useInertiaForm(inertiaInitialData);
  const reactForm = useReactHookForm<T>({
    resolver: zodResolver(schema),
    defaultValues: inertiaInitialData,
  });

  const onSubmit = reactForm.handleSubmit((data) => {
    inertiaForm.transform(() => data);
    inertiaForm.submit(method, submitRoute);
  });

  return {
    ...reactForm,
    inertiaForm,
    onSubmit,
    processing: inertiaForm.processing,
    errors: inertiaForm.errors,
  };
}
```

### 12.2 Contoh Penggunaan

```tsx
// Pages/Public/OrderForm.tsx
import { useHybridForm } from '@/hooks/useHybridForm';
import { orderSchema } from '@/lib/validations';

export default function OrderForm({ designs, sizes, priceTiers }) {
  const { register, handleSubmit, formState: { errors }, processing } = useHybridForm(
    orderSchema,
    { customer_name: '', customer_phone: '', items: [] },
    route('orders.store')
  );

  return (
    <form onSubmit={handleSubmit}>
      <Input
        label="Nama Pelanggan"
        {...register('customer_name')}
        error={errors.customer_name?.message}
      />
      {/* ... */}
      <Button type="submit" disabled={processing}>
        {processing ? 'Menghantar...' : 'Tempah Sekarang'}
      </Button>
    </form>
  );
}
```

---

## 13. Tailwind CSS v4 Compatibility

Konfigurasi semasa menggunakan Tailwind v4 dengan `@theme` dalam `app.css`. Ini **serasi sepenuhnya dengan React** — tiada perubahan diperlukan pada konfigurasi Tailwind.

```css
/* resources/css/app.css — Kekal sama */
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';
@source '../**/*.ts';
@source '../**/*.tsx';   /* Tambah ini */

@theme {
  --font-heading: 'Inter', ui-sans-serif, system-ui, sans-serif;
  --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
  --color-brand: #d91c5c;
  --color-brand-50: #fef1f5;
  /* ...semua warna dan komponen custom kekal */
}

@layer base { /* ... */ }
@layer components { /* ... */ }
```

---

## 14. Performance Considerations

### 14.1 Code Splitting

Inertia secara automatik melakukan code splitting berdasarkan page components:

```tsx
// app.tsx — Gunakan dynamic import
resolve: (name) => {
  const pages = import.meta.glob('./Pages/**/*.tsx');
  return pages[`./Pages/${name}.tsx`](); // Lazy load setiap page
},
```

### 14.2 Image Optimization

- Gunakan `loading="lazy"` untuk gambar di bawah fold.
- Pertimbangkan Laravel Media Library untuk generate thumbnails.
- Gunakan format WebP/AVIF jika boleh.

### 14.3 State Optimization

- Gunakan `React.memo` untuk komponen yang jarang berubah (Table rows, Sidebar items).
- Elakkan lifting state terlalu tinggi — gunakan local state sebanyak mungkin.

---

## 15. Kesimpulan

Migrasi ke **Inertia + React** membolehkan StickerTermurah memodenkan frontend tanpa menulis API terpisah. Semua logik backend Laravel kekal utuh — hanya layer presentation (view) yang ditukar kepada React components.

**Kelebihan utama:**
- ✅ Single Page Application (SPA) experience tanpa API boilerplate
- ✅ Type safety penuh dengan TypeScript
- ✅ Reusable components untuk konsistensi UI
- ✅ React ecosystem (hooks, form libraries, dsb.)
- ✅ Mudah di-maintain — satu fail Blade root sahaja

**Anggaran masa:** 4 minggu (part-time) atau 2 minggu (full-time)

---

*Dokumen ini dihasilkan pada 26 Mei 2026. Sila kemas kini jika terdapat perubahan keperluan atau skop.*
