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
