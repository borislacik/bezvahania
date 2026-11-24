<?php

use App\Mail\ContactMessageAdminMail;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => Mail::fake());

function contactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jana Kováčová',
        'email' => 'jana@example.com',
        'phone' => '0900 111 222',
        'message' => 'Dobrý deň, chceli by sme workshop pre firmu.',
    ], $overrides);
}

it('shows the contact section with details from the settings', function () {
    Setting::put(['contact_phone' => '+421 911 222 333']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('+421 911 222 333')
        ->assertSee('tel:+421911222333', escape: false);
});

it('stores the message and notifies the operator', function () {
    Setting::put(['notification_email' => 'majitel@example.com']);

    $this->from(route('home'))
        ->post(route('contact.store'), contactPayload())
        ->assertRedirect(route('home').'#kontaktny-formular')
        ->assertSessionHas('status', __('contact.sent'));

    $message = ContactMessage::query()->sole();
    expect($message->email)->toBe('jana@example.com')
        ->and($message->consent_at)->not->toBeNull()
        ->and($message->handled_at)->toBeNull();

    Mail::assertQueued(ContactMessageAdminMail::class, fn ($mail) => $mail->hasTo('majitel@example.com') && $mail->hasReplyTo('jana@example.com'));
});

it('shows the short contact form on other pages and returns to it after sending', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-testid="contact-cta-form"', escape: false)
        ->assertSee(__('contact.quick.title'));

    $this->from(route('legal.terms'))
        ->post(route('contact.store'), contactPayload(['phone' => null]))
        ->assertRedirect(route('legal.terms').'#kontaktny-formular');

    $this->from(route('legal.terms'))
        ->post(route('contact.store'), contactPayload(['message' => '']))
        ->assertRedirect(route('legal.terms').'#kontaktny-formular')
        ->assertSessionHasErrors('message');

    expect(ContactMessage::query()->count())->toBe(1);
    Mail::assertQueued(ContactMessageAdminMail::class, fn ($mail) => $mail->hasTo('info@bezvahania.sk'));
});

it('never redirects to a foreign host after sending', function () {
    $this->from('https://evil.example/stranka')
        ->post(route('contact.store'), contactPayload())
        ->assertRedirect(route('home').'#kontaktny-formular');
});

it('needs no consent checkbox but shows the privacy notice and stores the phone', function () {
    $this->get(route('home'))
        ->assertSee(__('contact.fields.phone'))
        ->assertDontSee('name="consent"', escape: false)
        ->assertSee('Odoslaním správy beriete na vedomie');

    $this->post(route('contact.store'), contactPayload(['phone' => '+421 905 123 456']))->assertSessionHasNoErrors();

    expect(ContactMessage::query()->sole()->phone)->toBe('+421 905 123 456');
});

it('validates the contact form', function (array $overrides, string $field) {
    $this->post(route('contact.store'), contactPayload($overrides))->assertSessionHasErrors($field);
    expect(ContactMessage::query()->count())->toBe(0);
})->with([
    'name' => [['name' => ''], 'name'],
    'email' => [['email' => 'zle'], 'email'],
    'short message' => [['message' => 'Ahoj'], 'message'],
    'phone' => [['phone' => 'zavolajte mi'], 'phone'],
    'honeypot' => [['website' => 'http://spam.example'], 'website'],
]);

it('rate limits the contact form', function () {
    foreach (range(1, 3) as $i) {
        $this->post(route('contact.store'), contactPayload(['name' => '']));
    }

    $this->post(route('contact.store'), contactPayload())->assertTooManyRequests();
});

it('renders the operator e-mail', function () {
    $message = ContactMessage::factory()->create(['message' => 'Otázka k termínu']);

    (new ContactMessageAdminMail($message))->assertSeeInHtml('Otázka k termínu')->assertSeeInHtml(e($message->name));
});

it('prunes contact messages older than the retention period', function () {
    $old = ContactMessage::factory()->create(['created_at' => now()->subMonths(13)]);
    $recent = ContactMessage::factory()->create(['created_at' => now()->subMonths(2)]);

    $this->artisan('model:prune', ['--model' => [ContactMessage::class]])->assertSuccessful();

    expect(ContactMessage::query()->find($old->id))->toBeNull()
        ->and(ContactMessage::query()->find($recent->id))->not->toBeNull();
});
