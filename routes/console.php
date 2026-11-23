<?php

use App\Actions\Bookings\AnonymizeOldBookings;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| Locally the "scheduler" Docker service runs `schedule:work`. On the hosting a cron
| entry runs `schedule:run` every minute (set up later, docs/hosting-websupport.md).
|
*/

Artisan::command('app:anonymize-bookings', function (AnonymizeOldBookings $anonymize): void {
    $count = $anonymize->handle();
    $this->info("Anonymized {$count} bookings older than ".AnonymizeOldBookings::RETENTION_YEARS.' years.');
})->purpose('Remove personal data from old bookings (GDPR)');

Schedule::command('model:prune', ['--model' => [ContactMessage::class]])->daily()->withoutOverlapping();
Schedule::command('app:anonymize-bookings')->daily()->withoutOverlapping();
Schedule::command('queue:work', ['--stop-when-empty', '--max-time=50'])
    ->everyMinute()
    ->withoutOverlapping()
    ->when(fn (): bool => app()->isProduction());
