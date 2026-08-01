<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactExtractionController as AdminContactExtractionController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DiscountController as AdminDiscountController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\JntController as AdminJntController;
use App\Http\Controllers\Admin\N8nSettingController as AdminN8nSettingController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PaymentSettingController as AdminPaymentSettingController;
use App\Http\Controllers\Admin\PriceSettingController as AdminPriceSettingController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\StickerDesignController as AdminStickerDesignController;
use App\Http\Controllers\Admin\StickerSizeController as AdminStickerSizeController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\UnderConstructionController as AdminUnderConstructionController;
use App\Http\Controllers\Admin\WatermarkController;
use App\Http\Controllers\Api\DesignController as ApiDesignController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Member\AuthController as MemberAuthController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\InvoiceController as MemberInvoiceController;
use App\Http\Controllers\Member\OrderController as MemberOrderController;
use App\Http\Controllers\Member\PaymentController as MemberPaymentController;
use App\Http\Controllers\Member\ProfileController as MemberProfileController;
use App\Http\Controllers\Member\TestimonialController as MemberTestimonialController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TestimonialController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::middleware('under_construction')->group(function () {
    Route::get('/', [FrontendController::class, 'home'])->name('home');
    Route::get('/semak-order', [FrontendController::class, 'lookupForm'])->name('orders.lookup-form');
    Route::post('/semak-order', [OrderController::class, 'lookup'])->name('orders.lookup');

    Route::get('/harga', [FrontendController::class, 'priceChecker'])->name('price.checker');
    Route::get('/testimoni', [TestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('/testimoni', [TestimonialController::class, 'store'])->name('testimonials.store');

    Route::prefix('ahli')->name('member.')->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('/daftar', [MemberAuthController::class, 'showRegister'])->name('register');
            Route::post('/daftar', [MemberAuthController::class, 'register'])->name('register.store');
            Route::get('/login', [MemberAuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [MemberAuthController::class, 'login'])->name('login.attempt');
        });

        Route::post('/logout', [MemberAuthController::class, 'logout'])->middleware('member')->name('logout');
        Route::get('/dashboard', MemberDashboardController::class)->middleware('member')->name('dashboard');
        Route::get('/orders', [MemberOrderController::class, 'index'])->middleware('member')->name('orders.index');
        Route::get('/orders/{order}', [MemberOrderController::class, 'show'])->middleware('member')->name('orders.show');
        Route::post('/orders/{order}/repeat', [MemberOrderController::class, 'repeat'])->middleware('member')->name('orders.repeat');
        Route::get('/invoices', [MemberInvoiceController::class, 'index'])->middleware('member')->name('invoices.index');
        Route::get('/invoices/{invoice}', [MemberInvoiceController::class, 'show'])->middleware('member')->name('invoices.show');
        Route::post('/invoices/{invoice}/payment', [MemberPaymentController::class, 'uploadReceipt'])->middleware('member')->name('invoices.payment.upload');
        Route::delete('/invoices/{invoice}/payment', [MemberPaymentController::class, 'cancelSubmission'])->middleware('member')->name('invoices.payment.cancel');
        Route::get('/testimoni', [MemberTestimonialController::class, 'index'])->middleware('member')->name('testimonials.index');
        Route::post('/testimoni', [MemberTestimonialController::class, 'store'])->middleware('member')->name('testimonials.store');
        Route::get('/profil', [MemberProfileController::class, 'edit'])->middleware('member')->name('profile.edit');
        Route::post('/profil', [MemberProfileController::class, 'update'])->middleware('member')->name('profile.update');
        Route::post('/profil/alamat', [MemberProfileController::class, 'storeAddress'])->middleware('member')->name('profile.address.store');
        Route::put('/profil/alamat/{address}', [MemberProfileController::class, 'updateAddress'])->middleware('member')->name('profile.address.update');
        Route::delete('/profil/alamat/{address}', [MemberProfileController::class, 'destroyAddress'])->middleware('member')->name('profile.address.destroy');
        Route::post('/profil/alamat/{address}/default', [MemberProfileController::class, 'setDefaultAddress'])->middleware('member')->name('profile.address.default');
        Route::get('/profil/katalaluan', [MemberProfileController::class, 'editPassword'])->middleware('member')->name('profile.password');
        Route::put('/profil/katalaluan', [MemberProfileController::class, 'updatePassword'])->middleware('member')->name('profile.password.update');
    });

    Route::get('/login', fn () => redirect()->route('member.login'))->name('login');
    Route::get('/api/designs', [ApiDesignController::class, 'index'])->name('api.designs.index');

    Route::middleware('auth')->group(function () {
        Route::get('/order', [FrontendController::class, 'orderForm'])->name('orders.create');
        Route::get('/order/ulang/{repeatOrder}', [FrontendController::class, 'orderForm'])->name('orders.repeat');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}/thank-you', [OrderController::class, 'thankYou'])->name('orders.thank-you');
    });
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');

    // Route return dari impersonation — perlu auth sahaja, bukan admin
    // (pengguna semasa adalah ahli semasa impersonate)
    Route::post('/return', [AdminAuthController::class, 'returnFromImpersonation'])->middleware('auth')->name('admin.return');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::get('/password', [AdminProfileController::class, 'editPassword'])->name('password.edit');
        Route::put('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update');

        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('designs', AdminStickerDesignController::class)->except(['show']);
        Route::get('/designs/tags/search', [AdminStickerDesignController::class, 'searchTags'])->name('designs.tags.search');
        Route::get('/designs/bulk/create', [AdminStickerDesignController::class, 'bulkCreate'])->name('designs.bulk.create');
        Route::post('/designs/bulk/store', [AdminStickerDesignController::class, 'bulkStore'])->name('designs.bulk.store');
        Route::get('/ori/{filename}', [AdminStickerDesignController::class, 'serveOriImage'])->name('ori.image');
        Route::get('/watermark', [WatermarkController::class, 'index'])->name('watermark.index');
        Route::post('/watermark/upload', [WatermarkController::class, 'upload'])->name('watermark.upload');
        Route::post('/watermark/config', [WatermarkController::class, 'saveConfig'])->name('watermark.config');
        Route::get('/watermark/{filename}', [WatermarkController::class, 'serve'])->name('watermark.serve');
        Route::delete('/watermark/{filename}', [WatermarkController::class, 'destroy'])->name('watermark.destroy');
        Route::resource('sizes', AdminStickerSizeController::class)->except(['show']);
        Route::resource('discounts', AdminDiscountController::class)->except(['show']);

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');
        Route::post('/customers/{customer}/login', [AdminCustomerController::class, 'loginAs'])->name('customers.login-as');
        Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
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
        Route::post('/invoices/{invoice}/approve', [AdminPaymentController::class, 'approve'])->name('invoices.approve');
        Route::post('/invoices/{invoice}/reject', [AdminPaymentController::class, 'reject'])->name('invoices.reject');
        Route::post('/invoices/{invoice}/reset', [AdminPaymentController::class, 'reset'])->name('invoices.reset');

        Route::get('/payment-settings', [AdminPaymentSettingController::class, 'index'])->name('payment-settings.index');
        Route::put('/payment-settings', [AdminPaymentSettingController::class, 'update'])->name('payment-settings.update');

        Route::get('/price-settings', [AdminPriceSettingController::class, 'index'])->name('price-settings.index');
        Route::post('/price-settings', [AdminPriceSettingController::class, 'store'])->name('price-settings.store');
        Route::put('/price-settings/{priceSetting}', [AdminPriceSettingController::class, 'update'])->name('price-settings.update');
        Route::delete('/price-settings/{priceSetting}', [AdminPriceSettingController::class, 'destroy'])->name('price-settings.destroy');

        Route::get('/testimonials', [AdminTestimonialController::class, 'index'])->name('testimonials.index');
        Route::get('/testimonials/{testimonial}/edit', [AdminTestimonialController::class, 'edit'])->name('testimonials.edit');
        Route::put('/testimonials/{testimonial}', [AdminTestimonialController::class, 'update'])->name('testimonials.update');
        Route::delete('/testimonials/{testimonial}', [AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');
        Route::post('/testimonials/{testimonial}/approve', [AdminTestimonialController::class, 'approve'])->name('testimonials.approve');
        Route::post('/testimonials/{testimonial}/reject', [AdminTestimonialController::class, 'reject'])->name('testimonials.reject');

        Route::get('/settings/n8n', [AdminN8nSettingController::class, 'edit'])->name('settings.n8n.edit');
        Route::put('/settings/n8n', [AdminN8nSettingController::class, 'update'])->name('settings.n8n.update');
        Route::post('/settings/n8n/test', [AdminN8nSettingController::class, 'test'])->name('settings.n8n.test');

        Route::get('/settings/under-construction', [AdminUnderConstructionController::class, 'edit'])->name('settings.under-construction.edit');
        Route::put('/settings/under-construction', [AdminUnderConstructionController::class, 'update'])->name('settings.under-construction.update');
    });
});

Route::bind('repeatOrder', fn (string $value) => Order::query()->findOrFail($value));
