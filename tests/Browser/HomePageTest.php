<?php

use App\Models\ContactMessage;
use App\Models\Testimonial;

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

it('renders Slovak letters with the brand fonts, not a fallback font', function () {
    $page = visit('/');

    // Faces covering Latin Extended-A (č, š, ľ, ť, ž, ň…) must be loaded for both brand fonts.
    $extended = $page->script(<<<'JS'
        () => document.fonts.ready
            .then(() => Promise.all([
                document.fonts.load('400 16px "Work Sans"', 'čšľťžň'),
                document.fonts.load('700 16px "Space Grotesk"', 'ČŠĽŤŽŇ'),
            ]))
            .then(() => [...new Set([...document.fonts]
                .filter((font) => font.status === 'loaded' && font.unicodeRange.includes('U+100-'))
                .map((font) => font.family.replaceAll('"', '')))])
        JS);

    expect($extended)->toContain('Work Sans', 'Space Grotesk');
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

it('adds up the pillars while scrolling and lands on the workshops from the menu', function () {
    $page = visit('/rezervacia')
        ->resize(1366, 768)
        ->click('[data-cookie-reject]');

    $page->click('header nav [data-nav="workshopy"]')->assertPathIs('/');
    $page->wait(1.5);

    $top = $page->script("() => Math.round(document.getElementById('workshopy').getBoundingClientRect().top)");
    expect($top)->toBeLessThan(200)->toBeGreaterThan(-50)
        ->and($page->script("() => document.querySelectorAll('[data-testid=\"pillars\"] .pillar-add.is-added').length"))->toBe(3);

    // The dark fact strip above the pillars moves just as slowly while the pillars are pinned.
    $offsets = $page->script(<<<'JS'
        () => {
            const strip = document.querySelector('[data-testid="pillars"] .bg-inverse-surface');
            const pillars = document.getElementById('preco');
            const start = pillars.getBoundingClientRect().top + window.scrollY - window.innerHeight * 0.3;
            const at = (y) => {
                window.scrollTo({ top: y, behavior: 'instant' });
                window.dispatchEvent(new Event('scroll'));
                return Math.round(strip.getBoundingClientRect().top);
            };
            return [at(start), at(start + 150)];
        }
        JS);
    expect($offsets[0] - $offsets[1])->toBeLessThan(80)->toBeGreaterThan(20);

    $page->script("() => { window.scrollTo({ top: 0, behavior: 'instant' }); window.dispatchEvent(new Event('scroll')); }");
    expect($page->script("() => document.querySelectorAll('[data-testid=\"pillars\"] .pillar-add.is-added').length"))->toBe(0);
    $page->assertNoJavaScriptErrors();
});

it('shows two testimonials next to the FAQ and switches to the next ones', function () {
    Testimonial::factory()->count(2)->create(['is_active' => true]);

    $page = visit('/')
        ->resize(1366, 768)
        ->click('[data-cookie-reject]');

    $visible = "() => [...document.querySelectorAll('[data-testid=\"testimonials\"] [data-testimonial-page]')]
        .filter((item) => item.offsetParent !== null).map((item) => item.dataset.testimonialPage)";

    expect($page->script($visible))->toBe(['0', '0']);

    $page->click('[aria-label="'.__('site.home.testimonials_page', ['page' => 2]).'"]');

    expect($page->script($visible))->toBe(['1', '1']);
    $page->assertNoJavaScriptErrors();
});
