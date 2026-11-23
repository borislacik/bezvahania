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
        'consent' => '1',
    ], $overrides);
}

it('shows the contact page with details from the settings', function () {
    Setting::put(['contact_phone' => '+421 911 222 333']);

    $this->get(route('contact.create'))
        ->assertOk()
        ->assertSee('+421 911 222 333')
        ->assertSee('tel:+421911222333', escape: false);
});

it('stores the message and notifies the operator', function () {
    Setting::put(['notification_email' => 'majitel@example.com']);

    $this->post(route('contact.store'), contactPayload())
        ->assertRedirect(route('contact.create'))
        ->assertSessionHas('status', __('contact.sent'));

    $message = ContactMessage::query()->sole();
    expect($message->email)->toBe('jana@example.com')
        ->and($message->consent_at)->not->toBeNull()
        ->and($message->handled_at)->toBeNull();

    Mail::assertQueued(ContactMessageAdminMail::class, fn ($mail) => $mail->hasTo('majitel@example.com') && $mail->hasReplyTo('jana@example.com'));
});

it('validates the contact form', function (array $overrides, string $field) {
    $this->post(route('contact.store'), contactPayload($overrides))->assertSessionHasErrors($field);
    expect(ContactMessage::query()->count())->toBe(0);
})->with([
    'name' => [['name' => ''], 'name'],
    'email' => [['email' => 'zle'], 'email'],
    'short message' => [['message' => 'Ahoj'], 'message'],
    'consent' => [['consent' => null], 'consent'],
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
