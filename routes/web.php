<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\ContactExtractionController as AdminContactExtractionController;
use App\Http\Controllers\Admin\JntController as AdminJntController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\StickerDesignController as AdminStickerDesignController;
use App\Http\Controllers\Admin\StickerSizeController as AdminStickerSizeController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Member\AuthController as MemberAuthController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\InvoiceController as MemberInvoiceController;
use App\Http\Controllers\Member\OrderController as MemberOrderController;
use App\Http\Controllers\Member\TestimonialController as MemberTestimonialController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TestimonialController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/semak-order', [FrontendController::class, 'lookupForm'])->name('orders.lookup-form');
Route::post('/semak-order', [OrderController::class, 'lookup'])->name('orders.lookup');

Route::get('/testimoni', [TestimonialController::class, 'index'])->name('testimonials.index');
Route::post('/testimoni', [TestimonialController::class, 'store'])->name('testimonials.store');

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
    Route::post('/orders/{order}/repeat', [MemberOrderController::class, 'repeat'])->middleware('auth')->name('orders.repeat');
    Route::get('/invoices/{invoice}', [MemberInvoiceController::class, 'show'])->middleware('auth')->name('invoices.show');
    Route::get('/testimoni', [MemberTestimonialController::class, 'index'])->middleware('auth')->name('testimonials.index');
    Route::post('/testimoni', [MemberTestimonialController::class, 'store'])->middleware('auth')->name('testimonials.store');
});

Route::get('/login', fn () => redirect()->route('member.login'))->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/order', [FrontendController::class, 'orderForm'])->name('orders.create');
    Route::get('/order/ulang/{repeatOrder}', [FrontendController::class, 'orderForm'])->name('orders.repeat');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}/thank-you', [OrderController::class, 'thankYou'])->name('orders.thank-you');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::get('/password', [AdminProfileController::class, 'editPassword'])->name('password.edit');
        Route::put('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update');

        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('designs', AdminStickerDesignController::class)->except(['show']);
        Route::get('/designs/bulk/create', [AdminStickerDesignController::class, 'bulkCreate'])->name('designs.bulk.create');
        Route::post('/designs/bulk/store', [AdminStickerDesignController::class, 'bulkStore'])->name('designs.bulk.store');
        Route::resource('sizes', AdminStickerSizeController::class)->except(['show']);

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/invoices/create', [AdminInvoiceController::class, 'create'])->name('invoices.create');
        Route::get('/invoices/manual', [AdminInvoiceController::class, 'createManual'])->name('invoices.manual.create');
        Route::post('/invoices/manual', [AdminInvoiceController::class, 'storeManual'])->name('invoices.manual.store');
        Route::post('/invoices', [AdminInvoiceController::class, 'storeFromMenu'])->name('invoices.store-from-menu');
        Route::get('/contacts/extract', [AdminContactExtractionController::class, 'index'])->name('contacts.extract');
        Route::post('/contacts/extract', [AdminContactExtractionController::class, 'extract'])->name('contacts.extract.run');
        Route::post('/contacts/extract/add-address', [AdminContactExtractionController::class, 'addAddress'])->name('contacts.extract.add-address');
        Route::post('/contacts/extract/add-user', [AdminContactExtractionController::class, 'addUser'])->name('contacts.extract.add-user');

        Route::get('/jnt', [AdminJntController::class, 'index'])->name('jnt.index');
        Route::post('/jnt/waybill', [AdminJntController::class, 'createWaybill'])->name('jnt.waybill');
        Route::post('/jnt/tracking', [AdminJntController::class, 'checkTracking'])->name('jnt.tracking');

        Route::post('/orders/{order}/invoice', [AdminInvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');

        Route::get('/testimonials', [AdminTestimonialController::class, 'index'])->name('testimonials.index');
        Route::get('/testimonials/{testimonial}/edit', [AdminTestimonialController::class, 'edit'])->name('testimonials.edit');
        Route::put('/testimonials/{testimonial}', [AdminTestimonialController::class, 'update'])->name('testimonials.update');
        Route::delete('/testimonials/{testimonial}', [AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');
        Route::post('/testimonials/{testimonial}/approve', [AdminTestimonialController::class, 'approve'])->name('testimonials.approve');
        Route::post('/testimonials/{testimonial}/reject', [AdminTestimonialController::class, 'reject'])->name('testimonials.reject');
    });
});

Route::bind('repeatOrder', fn (string $value) => Order::query()->findOrFail($value));

