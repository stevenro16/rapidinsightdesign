<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
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
});

Route::post('/login',   [LoginController::class, 'store'])->name('login.store')->withoutMiddleware('guest');
Route::post('/logout',  [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

/* ─── ShowRoom (all authenticated users) ───────────────────────────────── */
Route::middleware('auth')->prefix('showroom')->group(function () {
    Route::get('/',         [ShowroomController::class, 'index'])->name('showroom.index');
    Route::get('/{showroomItem}', [ShowroomController::class, 'show'])->name('showroom.show');
});

/* ─── Staff portal ──────────────────────────────────────────────────────── */
Route::middleware(['auth', 'role:staff,admin'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard',          [Staff\DashboardController::class,  'index'])->name('dashboard');
    Route::get('/customers',          [Staff\CustomerController::class,   'index'])->name('customers.index');
    Route::get('/customers/{user}',   [Staff\CustomerController::class,   'show'])->name('customers.show');
    Route::get('/inquiries',          [Staff\InquiryController::class,    'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [Staff\InquiryController::class,   'show'])->name('inquiries.show');
    Route::patch('/inquiries/{inquiry}', [Staff\InquiryController::class, 'update'])->name('inquiries.update');
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
    Route::post('/showcase',   [Admin\ShowcaseController::class, 'store'])->name('showcase.store');
    Route::put('/showcase/{showroomItem}',    [Admin\ShowcaseController::class, 'update'])->name('showcase.update');
    Route::delete('/showcase/{showroomItem}', [Admin\ShowcaseController::class, 'destroy'])->name('showcase.destroy');
    Route::post('/showcase/{showroomItem}/grant/{user}',  [Admin\ShowcaseController::class, 'grantAccess'])->name('showcase.grant');
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
