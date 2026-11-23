@php
    $code = (string) ($code ?? $exception->getStatusCode());
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex" />
    <title>{{ __("errors.{$code}.title") }} | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml" />
    @if (is_file(public_path('build/manifest.json')))
        @vite (['resources/css/app.css'])
    @endif
</head>
<body class="bg-surface text-on-surface flex min-h-screen items-center justify-center p-6 font-sans antialiased">
    <main class="card flex max-w-lg flex-col items-center gap-4 p-10 text-center" data-testid="error-page">
        <span class="bg-primary-container text-on-primary flex size-14 items-center justify-center rounded-xl">
            <x-site.icon name="medical_services" class="size-8" />
        </span>
        <p class="font-heading text-headline-xl text-primary font-bold">{{ $code }}</p>
        <h1 class="text-headline-lg font-bold">{{ __("errors.{$code}.title") }}</h1>
        <p class="text-body-md text-on-surface-variant">{{ __("errors.{$code}.text") }}</p>
        @if ($code !== '503')
            <a href="{{ url('/') }}" class="btn-primary mt-2">{{ __('errors.home') }}</a>
        @endif
    </main>
</body>
</html>
