<?php

use App\Models\ContactMessage;

it('shows the home page without JavaScript errors', function () {
    visit('/')
        ->assertSee(__('site.home.title_highlight'))
        ->assertNoJavaScriptErrors();
});

it('loads the self-hosted brand fonts', function () {
    $page = visit('/');

    $loaded = $page->script(<<<'JS'
        () => document.fonts.ready.then(() => [...document.fonts]
            .filter((font) => font.status === 'loaded')
            .map((font) => font.family.replaceAll('"', '')))
        JS);

    expect($loaded)->toContain('Space Grotesk', 'Work Sans');
});

it('renders every public page without JavaScript errors', function () {
    $pages = visit(['/', '/workshopy/kpr-a-aed', '/rezervacia', '/ochrana-osobnych-udajov', '/podmienky-rezervacie', '/cookies']);

    $pages->assertNoJavaScriptErrors()->assertNoConsoleLogs();
});

it('stores the cookie choice and keeps analytics off after rejecting', function () {
    $page = visit('/')
        ->assertVisible('[data-testid="cookie-banner"]')
        ->click(__('cookies.reject'))
        ->assertMissing('[data-testid="cookie-banner"]');

    $consent = $page->script("() => JSON.parse(localStorage.getItem('bv-cookie-consent'))");

    expect($consent['analytics'])->toBeFalse()
        ->and($page->script('() => window.__bvGaLoaded === true'))->toBeFalse();
});

it('sends a message from the contact box on the home page', function () {
    visit('/')
        ->click('[data-cookie-reject]')
        ->type('#cta-name', 'Ján Návštevník')
        ->type('#cta-email', 'jan@example.com')
        ->type('#cta-phone', '+421 905 111 222')
        ->type('#cta-message', 'Dobrý deň, mali by ste voľno aj v sobotu?')
        ->press(__('contact.submit'))
        ->assertSee(__('contact.sent'))
        ->assertNoJavaScriptErrors();

    expect(ContactMessage::query()->where('email', 'jan@example.com')->value('phone'))->toBe('+421 905 111 222');
});

it('scrolls to the home page section from the menu, also from another page', function () {
    $page = visit('/rezervacia')
        ->click('[data-cookie-reject]')
        ->click('header nav [data-nav="otazky"]')
        ->assertPathIs('/')
        ->assertSee(__('site.home.faq_title'));

    $page->wait(1);
    $top = $page->script("() => Math.round(document.getElementById('otazky').getBoundingClientRect().top)");
    expect($top)->toBeLessThan(200)->toBeGreaterThan(-50);

    $page->click('header nav [data-nav="kontakt"]');
    $page->wait(1);
    $top = $page->script("() => Math.round(document.getElementById('kontakt').getBoundingClientRect().top)");
    expect($top)->toBeLessThan(200)->toBeGreaterThan(-50);
    $page->assertNoJavaScriptErrors();
});
