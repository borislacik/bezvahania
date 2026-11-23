<?php

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('creates an administrator', function () {
    $this->artisan('app:create-admin', [
        '--name' => 'Majiteľ',
        '--email' => 'majitel@example.com',
        '--password' => 'velmi-silne-heslo',
    ])->assertSuccessful();

    $user = User::query()->where('email', 'majitel@example.com')->sole();
    expect(Hash::check('velmi-silne-heslo', $user->password))->toBeTrue();
});

it('rejects a weak password and a duplicate e-mail', function () {
    User::factory()->create(['email' => 'existuje@example.com']);

    $this->artisan('app:create-admin', ['--name' => 'A', '--email' => 'novy@example.com', '--password' => 'kratke'])->assertFailed();
    $this->artisan('app:create-admin', ['--name' => 'A', '--email' => 'existuje@example.com', '--password' => 'velmi-silne-heslo'])->assertFailed();

    expect(User::query()->count())->toBe(1);
});

it('anonymizes bookings older than three years', function () {
    $old = Booking::factory()->create(['date' => now()->subYears(3)->subDay()->toDateString(), 'note' => 'Súkromná poznámka']);
    $recent = Booking::factory()->create(['date' => now()->subYears(2)->toDateString()]);

    $this->artisan('app:anonymize-bookings')->assertSuccessful();

    $old->refresh();
    expect($old->contact_name)->toBe('—')
        ->and($old->email)->toBe('—')
        ->and($old->phone)->toBe('—')
        ->and($old->note)->toBeNull()
        ->and($old->anonymized_at)->not->toBeNull()
        ->and($recent->refresh()->anonymized_at)->toBeNull();
});
