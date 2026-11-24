<?php

use App\Http\Middleware\AddNoIndexHeader;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;

it('serves the admin only on the admin domain', function () {
    $this->get('http://admin.bezvahania.localhost/login')
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

    $this->get('http://bezvahania.localhost/login')->assertNotFound();
});

it('does not serve public pages on the admin domain', function (string $path) {
    $this->get('http://admin.bezvahania.localhost'.$path)->assertNotFound();
})->with(['/kontakt', '/workshopy', '/rezervacia', '/sitemap.xml']);

it('redirects guests from the admin dashboard to the login', function () {
    $this->get('http://admin.bezvahania.localhost/')->assertRedirect('http://admin.bezvahania.localhost/login');
});

it('reads both domains from the configuration', function () {
    expect(config('app.domain'))->toBe('bezvahania.localhost')
        ->and(config('app.admin_domain'))->toBe('admin.bezvahania.localhost')
        ->and(route('home'))->toBe('http://bezvahania.localhost');
});

it('re-applies only the noindex header on Livewire requests, never the session stack', function () {
    // Regression: making EncryptCookies/StartSession persistent ran them twice on every Livewire
    // request (e.g. dashboard widget polling), created a new empty session and logged the admin out.
    $persistent = Livewire\Livewire::getPersistentMiddleware();

    expect($persistent)->toContain(AddNoIndexHeader::class)
        ->not->toContain(EncryptCookies::class)
        ->not->toContain(StartSession::class)
        ->not->toContain(AddQueuedCookiesToResponse::class);
});
