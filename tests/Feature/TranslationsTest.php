<?php

use App\Support\UntranslatedTextScanner;
use Illuminate\Support\Facades\File;

it('has no untranslated texts in Blade templates', function () {
    $scanner = new UntranslatedTextScanner;
    $problems = [];

    foreach (File::allFiles(resource_path('views')) as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        foreach ($scanner->scan($file->getContents()) as $finding) {
            $problems[] = "{$file->getRelativePathname()}:{$finding['line']} {$finding['text']}";
        }
    }

    expect($problems)->toBe([]);
});

it('uses Slovak as the application locale', function () {
    expect(app()->getLocale())->toBe('sk')
        ->and(config('app.fallback_locale'))->toBe('sk')
        ->and(config('app.timezone'))->toBe('Europe/Bratislava')
        ->and(__('validation.required', ['attribute' => 'e-mail']))->toContain('e-mail')
        ->and(__('validation.required'))->not->toBe('validation.required');
});
