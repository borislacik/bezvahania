<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website (APP_DOMAIN)
|--------------------------------------------------------------------------
|
| All public routes are bound to the public domain, the admin lives on ADMIN_DOMAIN
| (app/Providers/Filament/AdminPanelProvider.php). Route names stay stable so a /{locale}
| prefix can be added later without breaking Slovak URLs.
|
*/

Route::domain(config('app.domain'))->group(function (): void {
    Route::get('/', [PageController::class, 'home'])->name('home');

    Route::get('/workshopy', [WorkshopController::class, 'index'])->name('workshops.index');
    Route::get('/workshopy/{course:slug}', [WorkshopController::class, 'show'])->name('workshops.show');

    Route::get('/rezervacia', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/rezervacia', [BookingController::class, 'store'])
        ->middleware('throttle:bookings')
        ->name('booking.store');
    Route::get('/rezervacia/{booking:reference}', [BookingController::class, 'show'])
        ->middleware('signed')
        ->name('booking.show');

    Route::get('/o-lektorovi', [PageController::class, 'about'])->name('about');
    Route::get('/caste-otazky', [PageController::class, 'faq'])->name('faq');

    Route::get('/kontakt', [ContactController::class, 'create'])->name('contact.create');
    Route::post('/kontakt', [ContactController::class, 'store'])
        ->middleware('throttle:contact')
        ->name('contact.store');

    Route::get('/ochrana-osobnych-udajov', [PageController::class, 'legal'])->defaults('key', 'privacy')->name('legal.privacy');
    Route::get('/podmienky-rezervacie', [PageController::class, 'legal'])->defaults('key', 'terms')->name('legal.terms');
    Route::get('/cookies', [PageController::class, 'legal'])->defaults('key', 'cookies')->name('legal.cookies');

    Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
    Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
});
