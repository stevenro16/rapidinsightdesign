<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CustomerAgreementController;
use App\Http\Controllers\CustomerInquiryController;
use App\Http\Controllers\CustomerInvoiceController;
use App\Http\Controllers\CustomerWorkOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public;
use App\Http\Controllers\ShowroomController;
use App\Http\Controllers\Staff;
use Illuminate\Support\Facades\Route;

/* ─── Public ────────────────────────────────────────────────────────────── */
Route::get('/',             [Public\HomeController::class,      'index'])->name('home');
Route::get('/how-we-work',  [Public\HowWeWorkController::class, 'index'])->name('how-we-work');
Route::get('/products',     [Public\ProductsController::class,  'index'])->name('products');
Route::get('/showcase',     [Public\ShowcaseController::class,  'index'])->name('showcase');
Route::get('/contact',      [Public\ContactController::class,   'index'])->name('contact');
Route::post('/contact',     [Public\ContactController::class,   'store'])->name('contact.store');

/* ─── Auth ──────────────────────────────────────────────────────────────── */
Route::middleware('guest')->group(function () {
    Route::get('/login',            [LoginController::class, 'create'])->name('login');
    Route::get('/forgot-password',  [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password',  [ResetPasswordController::class, 'store'])->name('password.update');
    Route::post('/register',        [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/login',   [LoginController::class, 'store'])->name('login.store')->withoutMiddleware('guest');
Route::post('/logout',  [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

/* ─── ShowRoom (all authenticated users) ───────────────────────────────── */
Route::middleware('auth')->prefix('showroom')->group(function () {
    Route::get('/',         [ShowroomController::class, 'index'])->name('showroom.index');
    Route::post('/{showroomItem}/request-access', [ShowroomController::class, 'requestAccess'])->name('showroom.request');
    Route::get('/{showroomItem}', [ShowroomController::class, 'show'])->name('showroom.show');
});

/* ─── Customer billing (signed-in customers view their shared invoices) ──── */
Route::middleware('auth')->group(function () {
    Route::get('/billing',                    [CustomerInvoiceController::class, 'index'])->name('billing.index');
    Route::get('/billing/{invoice}',          [CustomerInvoiceController::class, 'show'])->name('billing.show');
    Route::get('/billing/{invoice}/pdf',      [CustomerInvoiceController::class, 'pdf'])->name('billing.pdf');
    Route::post('/billing/{invoice}/payment', [CustomerInvoiceController::class, 'payment'])->name('billing.payment.store');

    // Account / profile — available to every signed-in user (customer, staff, admin)
    Route::get('/profile',            [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

/* ─── Customer dashboard + agreements ───────────────────────────────────── */
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/agreements',                  [CustomerAgreementController::class, 'index'])->name('agreements.index');
    Route::get('/agreements/{agreement}',      [CustomerAgreementController::class, 'show'])->name('agreements.show');
    Route::post('/agreements/{agreement}/sign',    [CustomerAgreementController::class, 'sign'])->name('agreements.sign');
    Route::post('/agreements/{agreement}/payment', [CustomerAgreementController::class, 'payment'])->name('agreements.payment.store');
    Route::post('/agreements/{agreement}/submit',  [CustomerAgreementController::class, 'submit'])->name('agreements.submit');

    Route::get('/work-orders',                 [CustomerWorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/work-orders/{workOrder}',     [CustomerWorkOrderController::class, 'show'])->name('work-orders.show');
    Route::post('/work-orders/{workOrder}/validate', [CustomerWorkOrderController::class, 'validateOrder'])->name('work-orders.validate');
    Route::post('/work-orders/{workOrder}/notes',    [CustomerWorkOrderController::class, 'storeNote'])->name('work-orders.notes.store');

    // Inquiries (submit & track + reply thread)
    Route::get('/inquiries',  [CustomerInquiryController::class, 'index'])->name('inquiries.index');
    Route::post('/inquiries', [CustomerInquiryController::class, 'store'])->name('inquiries.store');
    Route::get('/inquiries/{inquiry}',        [CustomerInquiryController::class, 'show'])->name('inquiries.show');
    Route::post('/inquiries/{inquiry}/notes', [CustomerInquiryController::class, 'storeNote'])->name('inquiries.notes.store');
});

/* ─── Staff portal ──────────────────────────────────────────────────────── */
Route::middleware(['auth', 'role:staff,admin'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard',          [Staff\DashboardController::class,  'index'])->name('dashboard');
    Route::get('/customers',          [Staff\CustomerController::class,   'index'])->name('customers.index');
    Route::get('/customers/{user}',   [Staff\CustomerController::class,   'show'])->name('customers.show');
    Route::patch('/customers/{user}',          [Staff\CustomerController::class, 'update'])->name('customers.update');
    Route::patch('/customers/{user}/password', [Staff\CustomerController::class, 'updatePassword'])->name('customers.password');
    Route::patch('/customers/{user}/toggle',   [Staff\CustomerController::class, 'toggleActive'])->name('customers.toggle');
    Route::delete('/customers/{user}/access/{showroomItem}', [Staff\CustomerController::class, 'revokeAccess'])->name('customers.access.revoke');

    // Customer notes
    Route::post('/customers/{user}/notes',            [Staff\CustomerNoteController::class, 'store'])->name('customers.notes.store');
    Route::delete('/customers/{user}/notes/{note}',   [Staff\CustomerNoteController::class, 'destroy'])->name('customers.notes.destroy');

    // Customer files
    Route::post('/customers/{user}/files',                    [Staff\CustomerFileController::class, 'store'])->name('customers.files.store');
    Route::get('/customers/{user}/files/{file}/download',     [Staff\CustomerFileController::class, 'download'])->name('customers.files.download');
    Route::delete('/customers/{user}/files/{file}',           [Staff\CustomerFileController::class, 'destroy'])->name('customers.files.destroy');

    // Customer invoices
    Route::post('/customers/{user}/invoices',                  [Staff\InvoiceController::class, 'store'])->name('customers.invoices.store');
    Route::get('/customers/{user}/invoices/{invoice}/edit',    [Staff\InvoiceController::class, 'edit'])->name('customers.invoices.edit');
    Route::patch('/customers/{user}/invoices/{invoice}',       [Staff\InvoiceController::class, 'update'])->name('customers.invoices.update');
    Route::delete('/customers/{user}/invoices/{invoice}',      [Staff\InvoiceController::class, 'destroy'])->name('customers.invoices.destroy');
    Route::get('/customers/{user}/invoices/{invoice}/pdf',     [Staff\InvoiceController::class, 'pdf'])->name('customers.invoices.pdf');

    // Agreements overview (all customers)
    Route::get('/agreements', [Staff\AgreementController::class, 'index'])->name('agreements.index');

    // Invoices overview (all customers)
    Route::get('/invoices', [Staff\InvoiceController::class, 'index'])->name('invoices.index');

    // Work orders
    Route::get('/work-orders',                  [Staff\WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::post('/customers/{user}/work-orders', [Staff\WorkOrderController::class, 'store'])->name('customers.work-orders.store');
    Route::get('/work-orders/{workOrder}',        [Staff\WorkOrderController::class, 'edit'])->name('work-orders.edit');
    Route::patch('/work-orders/{workOrder}',      [Staff\WorkOrderController::class, 'update'])->name('work-orders.update');
    Route::patch('/work-orders/{workOrder}/status', [Staff\WorkOrderController::class, 'updateStatus'])->name('work-orders.status');
    Route::post('/work-orders/{workOrder}/notes',   [Staff\WorkOrderController::class, 'storeNote'])->name('work-orders.notes.store');
    Route::delete('/work-orders/{workOrder}/notes/{note}', [Staff\WorkOrderController::class, 'destroyNote'])->name('work-orders.notes.destroy');
    Route::post('/work-orders/{workOrder}/agreements/{agreement}/attach', [Staff\WorkOrderController::class, 'attachAgreement'])->name('work-orders.agreements.attach');
    Route::delete('/work-orders/{workOrder}/agreements/{agreement}', [Staff\WorkOrderController::class, 'detachAgreement'])->name('work-orders.agreements.detach');
    Route::delete('/work-orders/{workOrder}',     [Staff\WorkOrderController::class, 'destroy'])->name('work-orders.destroy');

    // Customer agreements
    Route::post('/customers/{user}/agreements',                      [Staff\AgreementController::class, 'store'])->name('customers.agreements.store');
    Route::get('/customers/{user}/agreements/{agreement}/edit',      [Staff\AgreementController::class, 'edit'])->name('customers.agreements.edit');
    Route::patch('/customers/{user}/agreements/{agreement}',         [Staff\AgreementController::class, 'update'])->name('customers.agreements.update');
    Route::post('/customers/{user}/agreements/{agreement}/send',     [Staff\AgreementController::class, 'send'])->name('customers.agreements.send');
    Route::post('/customers/{user}/agreements/{agreement}/complete', [Staff\AgreementController::class, 'complete'])->name('customers.agreements.complete');
    Route::post('/customers/{user}/agreements/{agreement}/reopen',   [Staff\AgreementController::class, 'reopen'])->name('customers.agreements.reopen');
    Route::post('/customers/{user}/agreements/{agreement}/cancel',   [Staff\AgreementController::class, 'cancel'])->name('customers.agreements.cancel');
    Route::get('/customers/{user}/agreements/{agreement}/pdf',       [Staff\AgreementController::class, 'pdf'])->name('customers.agreements.pdf');
    Route::delete('/customers/{user}/agreements/{agreement}',        [Staff\AgreementController::class, 'destroy'])->name('customers.agreements.destroy');
    Route::post('/customers/{user}/agreements/{agreement}/invoices', [Staff\InvoiceController::class, 'storeFromAgreement'])->name('customers.agreements.invoices.store');
    Route::post('/customers/{user}/agreements/{agreement}/payments',                    [Staff\AgreementController::class, 'storePayment'])->name('customers.agreements.payments.store');
    Route::patch('/customers/{user}/agreements/{agreement}/payments/{payment}/confirm', [Staff\AgreementController::class, 'confirmPayment'])->name('customers.agreements.payments.confirm');
    Route::delete('/customers/{user}/agreements/{agreement}/payments/{payment}',        [Staff\AgreementController::class, 'destroyPayment'])->name('customers.agreements.payments.destroy');
    Route::get('/inquiries',          [Staff\InquiryController::class,    'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [Staff\InquiryController::class,   'show'])->name('inquiries.show');
    Route::patch('/inquiries/{inquiry}', [Staff\InquiryController::class, 'update'])->name('inquiries.update');
    Route::post('/inquiries/{inquiry}/notes', [Staff\InquiryController::class, 'storeNote'])->name('inquiries.notes.store');
});

/* ─── Admin portal ──────────────────────────────────────────────────────── */
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users',           [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create',    [Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users',          [Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}',    [Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Showcase management
    Route::get('/showcase',    [Admin\ShowcaseController::class, 'index'])->name('showcase.index');
    Route::post('/showcase/reorder', [Admin\ShowcaseController::class, 'reorder'])->name('showcase.reorder');
    Route::post('/showcase',   [Admin\ShowcaseController::class, 'store'])->name('showcase.store');
    Route::put('/showcase/{showroomItem}',    [Admin\ShowcaseController::class, 'update'])->name('showcase.update');
    Route::delete('/showcase/{showroomItem}', [Admin\ShowcaseController::class, 'destroy'])->name('showcase.destroy');
    Route::post('/showcase/{showroomItem}/grant/{user}',  [Admin\ShowcaseController::class, 'grantAccess'])->name('showcase.grant');
    Route::post('/showcase/{showroomItem}/approve/{user}', [Admin\ShowcaseController::class, 'approveAccess'])->name('showcase.approve');
    Route::delete('/showcase/{showroomItem}/revoke/{user}', [Admin\ShowcaseController::class, 'revokeAccess'])->name('showcase.revoke');
    Route::get('/showcase/{showroomItem}/slides',             [Admin\ShowcaseSlideController::class, 'index'])->name('showcase.slides.index');
    Route::post('/showcase/{showroomItem}/slides',            [Admin\ShowcaseSlideController::class, 'store'])->name('showcase.slides.store');
    Route::put('/showcase/{showroomItem}/slides/{slide}',     [Admin\ShowcaseSlideController::class, 'update'])->name('showcase.slides.update');
    Route::delete('/showcase/{showroomItem}/slides/{slide}',  [Admin\ShowcaseSlideController::class, 'destroy'])->name('showcase.slides.destroy');

    // Site content
    Route::get('/content',  [Admin\ContentController::class, 'index'])->name('content.index');
    Route::post('/content', [Admin\ContentController::class, 'update'])->name('content.update');

    // Prospects (literal paths before {prospect} so they aren't captured by model binding)
    Route::get('/prospects',                   [Admin\ProspectController::class, 'index'])->name('prospects.index');
    Route::get('/prospects/data',              [Admin\ProspectController::class, 'data'])->name('prospects.data');
    Route::get('/prospects/export',            [Admin\ProspectController::class, 'export'])->name('prospects.export');
    Route::post('/prospects/search',           [Admin\ProspectController::class, 'search'])->name('prospects.search');
    Route::get('/prospects/{prospect}',        [Admin\ProspectController::class, 'show'])->name('prospects.show');
    Route::post('/prospects/{prospect}/scan',  [Admin\ProspectController::class, 'scan'])->name('prospects.scan');
    Route::patch('/prospects/{prospect}',      [Admin\ProspectController::class, 'updateStatus'])->name('prospects.update');
    Route::post('/prospects/{prospect}/notes', [Admin\ProspectController::class, 'storeNote'])->name('prospects.notes.store');
    Route::delete('/prospects/{prospect}',     [Admin\ProspectController::class, 'destroy'])->name('prospects.destroy');
});
