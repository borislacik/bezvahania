<?php

use App\Models\User;
use Database\Seeders\ContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Unit tests run without the framework. Feature and Browser tests boot Laravel
| against the MariaDB test database (bezvahania_testing).
|
*/

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->beforeEach(fn () => $this->withoutVite())
    ->in('Feature');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => $this->seed(ContentSeeder::class))
    ->in('Browser');

// Browser tests reach the app on the public domain (*.localhost resolves to loopback).
pest()->browser()->withHost('bezvahania.localhost');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Logs in an administrator for Filament (Livewire) tests.
 */
function actingAsAdmin(): User
{
    $admin = User::factory()->create();
    test()->actingAs($admin);
    Filament::setCurrentPanel('admin');

    return $admin;
}
