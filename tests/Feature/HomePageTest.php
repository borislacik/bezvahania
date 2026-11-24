<?php

use App\Models\Course;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Database\Seeders\ContentSeeder;

beforeEach(function () {
    $this->seed(ContentSeeder::class);
});

it('renders the home page in Slovak with the seeded content', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<html lang="sk">', escape: false)
        ->assertSee(__('site.home.title_highlight'))
        ->assertSee(Course::query()->where('slug', 'kpr-a-aed')->value('slug'))
        ->assertSee(TeamMember::query()->value('name'))
        ->assertSee(e(Testimonial::query()->value('author')), escape: false)
        ->assertSee(__('cookies.title'));
});

it('renders the SEO tags from the admin', function () {
    Page::query()->where('key', 'home')->update([
        'seo_title' => ['sk' => 'Vlastný titulok z adminu'],
        'seo_description' => ['sk' => 'Vlastný popis z adminu'],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<title>Vlastný titulok z adminu | Bez váhania</title>', escape: false)
        ->assertSee('<meta name="description" content="Vlastný popis z adminu" />', escape: false)
        ->assertSee('<meta property="og:title" content="Vlastný titulok z adminu | Bez váhania" />', escape: false)
        ->assertSee('<link rel="canonical" href="http://bezvahania.localhost" />', escape: false)
        ->assertSee('"@type":"LocalBusiness"', escape: false)
        ->assertSee('<meta property="og:image" content="', escape: false);
});

it('exposes the health check endpoint', function () {
    $this->get('/up')->assertOk();
});

it('shows the three pillars before the workshops and answers how a workshop runs in the FAQ', function () {
    $pillars = __('site.home.pillars');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-testid="pillars"', escape: false)
        ->assertSeeInOrder([
            __('site.home.fact_title'),
            __('site.home.pillars_title'),
            ...array_column($pillars, 'title'),
            __('site.home.workshops_title'),
            __('site.home.faq_title'),
            'Ako prebieha workshop?',
        ])
        ->assertDontSee('id="ako-prebieha"', escape: false);
});

it('shows the business details in the footer by default', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Boris Lacík', 'IČO: 54842280', 'DIČ: 1077502657', 'Nie som platiteľ DPH', 'Studenohorská 2061/17, 841 03 Bratislava']);
});

it('introduces the instructor and keeps the hero without key figures', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Boris Lacík')
        ->assertSee('Profesionálny hasič a zdravotnícky záchranár')
        ->assertSee(__('site.home.instructor_badge_value'))
        ->assertSee('9+')
        ->assertDontSee('rokov v záchrannej službe');
});
