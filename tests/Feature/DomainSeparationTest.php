<?php

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
