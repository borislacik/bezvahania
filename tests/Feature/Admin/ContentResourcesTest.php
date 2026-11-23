<?php

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\TeamMembers\Pages\ManageTeamMembers;
use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Faq;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    actingAsAdmin();
});

it('lists, creates and edits workshops', function () {
    $existing = Course::factory()->create(['name' => 'Starý názov']);

    Livewire::test(ListCourses::class)->assertOk()->assertCanSeeTableRecords([$existing]);

    Livewire::test(CreateCourse::class)
        ->fillForm([
            'name' => 'Nový workshop',
            'slug' => 'novy-workshop',
            'price' => 220,
            'duration_label' => 'cca 3 hodiny',
            'short_description' => 'Krátky popis nového workshopu.',
            'is_active' => true,
            'sort_order' => 4,
            'seo_title' => 'SEO titulok',
            'seo_description' => 'SEO popis workshopu',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $course = Course::query()->where('slug', 'novy-workshop')->sole();
    expect($course->name)->toBe('Nový workshop')
        ->and($course->getTranslation('name', 'sk'))->toBe('Nový workshop')
        ->and($course->seo_title)->toBe('SEO titulok');

    Livewire::test(EditCourse::class, ['record' => $existing->getRouteKey()])
        ->assertSchemaStateSet(['name' => 'Starý názov'])
        ->fillForm(['name' => 'Upravený názov'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($existing->refresh()->name)->toBe('Upravený názov');
});

it('converts an uploaded workshop image to WebP', function () {
    $course = Course::factory()->create();

    Livewire::test(EditCourse::class, ['record' => $course->getRouteKey()])
        ->fillForm(['image' => UploadedFile::fake()->image('fotka.jpg', 2400, 1200)])
        ->call('save')
        ->assertHasNoFormErrors();

    $path = $course->refresh()->image;
    expect($path)->toStartWith('courses/')->toEndWith('.webp');
    Storage::disk('public')->assertExists($path);

    $size = getimagesizefromstring(Storage::disk('public')->get($path));
    expect($size['mime'])->toBe('image/webp')->and($size[0])->toBe(1920);
});

it('validates unique workshop slugs and protects workshops with bookings', function () {
    $course = Course::factory()->create(['slug' => 'obsadeny']);

    Livewire::test(CreateCourse::class)
        ->fillForm(['name' => 'X', 'slug' => 'obsadeny', 'price' => 1, 'duration_label' => 'x', 'short_description' => 'x'])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);

    Booking::factory()->for($course)->create();

    Livewire::test(EditCourse::class, ['record' => $course->getRouteKey()])->assertActionHidden('delete');
});

it('manages FAQ, testimonials and team members', function () {
    $faq = Faq::factory()->create(['question' => 'Pôvodná otázka?']);
    $testimonial = Testimonial::factory()->create();
    $member = TeamMember::factory()->create(['role' => 'Lektor']);

    Livewire::test(ManageFaqs::class)
        ->assertCanSeeTableRecords([$faq])
        ->callTableAction('edit', $faq, data: ['question' => 'Nová otázka?'])
        ->assertHasNoTableActionErrors();
    expect($faq->refresh()->question)->toBe('Nová otázka?');

    Livewire::test(ManageTestimonials::class)
        ->assertCanSeeTableRecords([$testimonial])
        ->callAction('create', data: ['author' => 'Nový autor', 'text' => 'Super workshop.', 'rating' => 5, 'sort_order' => 2, 'is_active' => true])
        ->assertHasNoActionErrors();
    expect(Testimonial::query()->where('author', 'Nový autor')->exists())->toBeTrue();

    Livewire::test(ManageTeamMembers::class)
        ->assertCanSeeTableRecords([$member])
        ->mountTableAction('edit', $member)
        ->assertTableActionDataSet(['role' => 'Lektor']);
});

it('edits page SEO and legal texts', function () {
    $page = Page::factory()->create(['key' => 'terms', 'title' => 'Podmienky']);

    Livewire::test(ListPages::class)->assertCanSeeTableRecords([$page]);

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertSchemaStateSet(['title' => 'Podmienky'])
        ->fillForm(['seo_title' => 'Podmienky rezervácie | SEO', 'seo_description' => 'Popis pre Google'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->refresh()->seo_title)->toBe('Podmienky rezervácie | SEO');
});

it('shows contact messages and marks them handled', function () {
    $message = ContactMessage::factory()->create();

    Livewire::test(ManageContactMessages::class)
        ->assertCanSeeTableRecords([$message])
        ->callTableAction('handled', $message)
        ->assertNotified(__('admin.actions.marked_handled'));

    expect($message->refresh()->handled_at)->not->toBeNull();
});
