<?php

use App\Models\Course;
use App\Models\Faq;
use App\Models\Page;
use Database\Seeders\ContentSeeder;

beforeEach(function () {
    $this->seed(ContentSeeder::class);
});

it('renders every public page', function (string $route, array $parameters = []) {
    $this->get(route($route, $parameters))
        ->assertOk()
        ->assertSee('<html lang="sk">', escape: false)
        ->assertSee('<link rel="canonical"', escape: false)
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
})->with([
    'home' => ['home'],
    'workshops' => ['workshops.index'],
    'workshop detail' => ['workshops.show', ['course' => 'kpr-a-aed']],
    'booking' => ['booking.create'],
    'about' => ['about'],
    'faq' => ['faq'],
    'contact' => ['contact.create'],
    'privacy' => ['legal.privacy'],
    'terms' => ['legal.terms'],
    'cookies' => ['legal.cookies'],
]);

it('shows the contact phone and e-mail, never the automatic booking address', function (string $route) {
    $this->get(route($route))
        ->assertOk()
        ->assertSee('+421 915 918 386')
        ->assertSee('href="tel:+421915918386"', escape: false)
        ->assertSee('href="mailto:info@bezvahania.sk"', escape: false)
        ->assertDontSee('rezervacie@bezvahania.sk');
})->with(['home', 'contact.create', 'faq']);

it('shows active workshops and hides inactive ones', function () {
    $inactive = Course::factory()->inactive()->create(['name' => 'Skrytý workshop']);

    $this->get(route('workshops.index'))
        ->assertOk()
        ->assertSee('KPR a záchrana života s AED')
        ->assertDontSee('Skrytý workshop');

    $this->get(route('workshops.show', $inactive))->assertNotFound();
});

it('renders the workshop detail with Course structured data', function () {
    $this->get(route('workshops.show', 'kpr-a-aed'))
        ->assertOk()
        ->assertSee('KPR a záchrana života s AED')
        ->assertSee('"@type":"Course"', escape: false)
        ->assertSee('"priceCurrency":"EUR"', escape: false)
        ->assertSee(route('booking.create', ['workshop' => 'kpr-a-aed']), escape: false);
});

it('renders the FAQ with FAQPage structured data', function () {
    $faq = Faq::query()->firstOrFail();

    $this->get(route('faq'))
        ->assertOk()
        ->assertSee(e($faq->question), escape: false)
        ->assertSee('"@type":"FAQPage"', escape: false);
});

it('renders legal page content from the admin and a fallback when empty', function () {
    $this->get(route('legal.cookies'))->assertOk()->assertSee('Google Analytics');

    Page::query()->where('key', 'privacy')->update(['content' => null]);
    $this->get(route('legal.privacy'))->assertOk()->assertSee('info@bezvahania.sk');

    Page::query()->where('key', 'privacy')->update(['content' => ['sk' => '<h2>Prevádzkovateľ</h2><p>Text GDPR</p>']]);
    $this->get(route('legal.privacy'))->assertOk()->assertSee('<h2>Prevádzkovateľ</h2>', escape: false);
});

it('serves a sitemap with all public pages and workshops', function () {
    $response = $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    foreach (['home', 'workshops.index', 'booking.create', 'about', 'faq', 'contact.create', 'legal.privacy'] as $route) {
        $response->assertSee('<loc>'.route($route).'</loc>', escape: false);
    }

    $response->assertSee('<loc>'.route('workshops.show', 'kpr-a-aed').'</loc>', escape: false);
});

it('blocks robots outside production and allows them in production', function () {
    $this->get(route('robots'))->assertOk()->assertSee('Disallow: /');

    $this->app->detectEnvironment(fn () => 'production');

    $this->get(route('robots'))
        ->assertOk()
        ->assertSee('Allow: /')
        ->assertSee('Sitemap: '.route('sitemap'));
});

it('does not index pages outside production', function () {
    $this->get(route('home'))->assertSee('<meta name="robots" content="noindex, nofollow" />', escape: false);
});

it('redirects www to the bare domain', function () {
    $this->get('http://www.bezvahania.localhost/workshopy?x=1')
        ->assertStatus(301)
        ->assertRedirect('http://bezvahania.localhost/workshopy?x=1');
});

it('sends HSTS only over HTTPS', function () {
    $this->get(route('home'))->assertHeaderMissing('Strict-Transport-Security');
    $this->get('https://bezvahania.localhost/')->assertHeader('Strict-Transport-Security');
});

it('uses only WebP images for content', function () {
    $html = $this->get(route('home'))->getContent();

    preg_match_all('/<img[^>]+src="([^"]+)"/', (string) $html, $matches);

    expect($matches[1])->not->toBeEmpty();
    foreach ($matches[1] as $src) {
        expect($src)->toEndWith('.webp');
    }
});
