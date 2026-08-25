<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CompanyDocumentController as AdminCompanyDocumentController;
use App\Http\Controllers\Admin\ContactExtractionController as AdminContactExtractionController;
use App\Http\Controllers\Admin\CustomerAddressController as AdminCustomerAddressController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\CustomerProjectController as AdminCustomerProjectController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DatabaseBackupController as AdminDatabaseBackupController;
use App\Http\Controllers\Admin\DiscountController as AdminDiscountController;
use App\Http\Controllers\Admin\GoogleContactController as AdminGoogleContactController;
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
use App\Http\Controllers\LegalController;
use App\Http\Controllers\Member\AuthController as MemberAuthController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\InvoiceController as MemberInvoiceController;
use App\Http\Controllers\Member\OrderController as MemberOrderController;
use App\Http\Controllers\Member\PaymentController as MemberPaymentController;
use App\Http\Controllers\Member\ProfileController as MemberProfileController;
use App\Http\Controllers\Member\ProjectController as MemberProjectController;
use App\Http\Controllers\Member\TestimonialController as MemberTestimonialController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TestimonialController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');

Route::get('/privacy-policy', [LegalController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-of-service', [LegalController::class, 'termsOfService'])->name('terms-of-service');

Route::middleware('under_construction')->group(function () {
    Route::get('/', [FrontendController::class, 'home'])->name('home');
    Route::get('/semak-order', [FrontendController::class, 'lookupForm'])->name('orders.lookup-form');
    Route::post('/semak-order', [OrderController::class, 'lookup'])->name('orders.lookup');

    Route::get('/harga', [FrontendController::class, 'priceChecker'])->name('price.checker');
    Route::get('/testimoni', [TestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('/testimoni', [TestimonialController::class, 'store'])->name('testimonials.store');

    Route::middleware('guest')->group(function () {
        Route::get('/ahli/lupa-kata-laluan', [MemberAuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/ahli/lupa-kata-laluan', [MemberAuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
        Route::get('/ahli/tetap-semula-kata-laluan/{token}', [MemberAuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/ahli/tetap-semula-kata-laluan', [MemberAuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::prefix('ahli')->name('member.')->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('/daftar', [MemberAuthController::class, 'showRegister'])->name('register');
            Route::post('/daftar', [MemberAuthController::class, 'register'])->name('register.store');
            Route::get('/login', [MemberAuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [MemberAuthController::class, 'login'])->name('login.attempt');
        });

        Route::post('/logout', [MemberAuthController::class, 'logout'])->middleware('member')->name('logout');
        Route::get('/dashboard', MemberDashboardController::class)->middleware('member')->name('dashboard');
        Route::get('/order', [FrontendController::class, 'orderForm'])->middleware('member')->name('orders.create');
        Route::get('/order/ulang/{repeatOrder}', [FrontendController::class, 'orderForm'])->middleware('member')->name('orders.repeat-form');
        Route::get('/orders', [MemberOrderController::class, 'index'])->middleware('member')->name('orders.index');
        Route::get('/orders/{order}', [MemberOrderController::class, 'show'])->middleware('member')->name('orders.show');
        Route::post('/orders/{order}/repeat', [MemberOrderController::class, 'repeat'])->middleware('member')->name('orders.repeat');
        Route::post('/orders/{order}/approve-price', [MemberOrderController::class, 'approvePrice'])->middleware('member')->name('orders.approve-price');
        Route::get('/projects', [MemberProjectController::class, 'index'])->middleware('member')->name('projects.index');
        Route::get('/projects/{project}/preview/{preview?}', [MemberProjectController::class, 'preview'])->middleware('member')->name('projects.preview');
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

    Route::get('/order', [FrontendController::class, 'orderForm'])->name('orders.create');

    Route::middleware('auth')->group(function () {
        Route::get('/order/ulang/{repeatOrder}', [FrontendController::class, 'orderForm'])->name('orders.repeat');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}/thank-you', [OrderController::class, 'thankYou'])->name('orders.thank-you');
    });
});

Route::get('/auth/google/callback', [AdminGoogleContactController::class, 'handleGoogleCallback'])
    ->middleware(['auth', 'admin'])
    ->name('admin.contacts.google.callback');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');

    // Route return dari impersonation — perlu auth sahaja, bukan admin
    // (pengguna semasa adalah ahli semasa impersonate)
    Route::post('/return', [AdminAuthController::class, 'returnFromImpersonation'])->middleware('auth')->name('return');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/database/backup', [AdminDatabaseBackupController::class, 'download'])->name('database.backup');
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::get('/password', [AdminProfileController::class, 'editPassword'])->name('password.edit');
        Route::put('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update');

        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('designs', AdminStickerDesignController::class)->except(['show']);
        Route::get('/designs/tags/search', [AdminStickerDesignController::class, 'searchTags'])->name('designs.tags.search');
        Route::put('/designs/tags/rename', [AdminStickerDesignController::class, 'renameTag'])->name('designs.tags.rename');
        Route::post('/designs/bulk/tag', [AdminStickerDesignController::class, 'bulkAddTag'])->name('designs.bulk.tag');
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
        Route::get('/projects', [AdminCustomerProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [AdminCustomerProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [AdminCustomerProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}/preview', [AdminCustomerProjectController::class, 'preview'])->name('projects.preview');
        Route::get('/projects/{project}/source/{source?}', [AdminCustomerProjectController::class, 'source'])->name('projects.source');
        Route::get('/projects/{project}/source-preview/{source?}', [AdminCustomerProjectController::class, 'sourcePreview'])->name('projects.source-preview');
        Route::delete('/projects/{project}', [AdminCustomerProjectController::class, 'destroy'])->name('projects.destroy');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [FrontendController::class, 'orderForm'])->name('orders.create');
        Route::get('/orders/{order}/edit', [AdminOrderController::class, 'edit'])->name('orders.edit');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::put('/orders/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('orders.tracking.update');
        Route::post('/orders/{order}/quote', [AdminOrderController::class, 'quote'])->name('orders.quote');
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/search', [AdminCustomerController::class, 'search'])->name('customers.search');
        Route::get('/customers/create', [AdminCustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [AdminCustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');
        Route::post('/customers/{customer}/reset-password', [AdminCustomerController::class, 'resetPassword'])->name('customers.reset-password');
        Route::post('/customers/{customer}/login', [AdminCustomerController::class, 'loginAs'])->name('customers.login-as');
        Route::get('/company-documents', [AdminCompanyDocumentController::class, 'index'])->name('company-documents.index');
        Route::post('/company-documents', [AdminCompanyDocumentController::class, 'store'])->name('company-documents.store');
        Route::get('/company-documents/{companyDocument}/download', [AdminCompanyDocumentController::class, 'download'])->name('company-documents.download');
        Route::get('/company-documents/{companyDocument}/preview', [AdminCompanyDocumentController::class, 'preview'])->name('company-documents.preview');
        Route::delete('/company-documents/{companyDocument}', [AdminCompanyDocumentController::class, 'destroy'])->name('company-documents.destroy');
        Route::get('/customer-addresses', [AdminCustomerAddressController::class, 'index'])->name('customer-addresses.index');
        Route::get('/customer-addresses/create', [AdminCustomerAddressController::class, 'create'])->name('customer-addresses.create');
        Route::post('/customer-addresses', [AdminCustomerAddressController::class, 'store'])->name('customer-addresses.store');
        Route::get('/customer-addresses/{customerAddress}/edit', [AdminCustomerAddressController::class, 'edit'])->name('customer-addresses.edit');
        Route::put('/customer-addresses/{customerAddress}', [AdminCustomerAddressController::class, 'update'])->name('customer-addresses.update');
        Route::delete('/customer-addresses/{customerAddress}', [AdminCustomerAddressController::class, 'destroy'])->name('customer-addresses.destroy');
        Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
        Route::put('/invoices/{invoice}/tracking', [AdminInvoiceController::class, 'updateTracking'])->name('invoices.tracking.update');
        Route::get('/invoices/create', [AdminInvoiceController::class, 'create'])->name('invoices.create');
        Route::get('/invoices/manual', [AdminInvoiceController::class, 'createManual'])->name('invoices.manual.create');
        Route::post('/invoices/manual', [AdminInvoiceController::class, 'storeManual'])->name('invoices.manual.store');
        Route::post('/invoices', [AdminInvoiceController::class, 'storeFromMenu'])->name('invoices.store-from-menu');
        Route::get('/invoices/{invoice}/edit', [AdminInvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [AdminInvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{invoice}', [AdminInvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/contacts/extract', [AdminContactExtractionController::class, 'index'])->name('contacts.extract');
        Route::get('/contacts/extract/customers/search', [AdminContactExtractionController::class, 'searchCustomers'])->name('contacts.extract.customers.search');
        Route::post('/contacts/extract', [AdminContactExtractionController::class, 'extract'])->name('contacts.extract.run');
        Route::get('/contacts/extract/add-address', fn () => redirect()->route('admin.contacts.extract'))->name('contacts.extract.add-address.get');
        Route::get('/contacts/extract/add-user', fn () => redirect()->route('admin.contacts.extract'))->name('contacts.extract.add-user.get');
        Route::post('/contacts/extract/add-address', [AdminContactExtractionController::class, 'addAddress'])->name('contacts.extract.add-address');
        Route::post('/contacts/extract/add-user', [AdminContactExtractionController::class, 'addUser'])->name('contacts.extract.add-user');
        Route::get('/contacts/google', [AdminGoogleContactController::class, 'index'])->name('contacts.google.index');
        Route::get('/contacts/google/create', [AdminGoogleContactController::class, 'create'])->name('contacts.google.create');
        Route::get('/contacts/google/connect', [AdminGoogleContactController::class, 'redirectToGoogle'])->name('contacts.google.connect');
        Route::post('/contacts/google/disconnect', [AdminGoogleContactController::class, 'disconnect'])->name('contacts.google.disconnect');
        Route::post('/contacts/google/manual', [AdminGoogleContactController::class, 'storeManual'])->name('contacts.google.manual.store');
        Route::post('/contacts/google/customer', [AdminGoogleContactController::class, 'storeCustomer'])->name('contacts.google.customer.store');
        Route::put('/contacts/google', [AdminGoogleContactController::class, 'update'])->name('contacts.google.update');
        Route::delete('/contacts/google/bulk', [AdminGoogleContactController::class, 'bulkDestroy'])->name('contacts.google.bulk-destroy');
        Route::delete('/contacts/google', [AdminGoogleContactController::class, 'destroy'])->name('contacts.google.destroy');

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

Route::get('/invoice/{invoice}/view', [PublicInvoiceController::class, 'show'])
    ->middleware('signed')
    ->name('invoices.public');

Route::bind('repeatOrder', fn (string $value) => Order::query()->findOrFail($value));
