@props (['seo'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <x-site.seo :seo="$seo" />
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml" />
    <meta name="theme-color" content="#e52320" />
    @fonts
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface text-body-md flex min-h-screen flex-col font-sans antialiased">
    <a
        href="#main"
        class="bg-primary-container text-on-primary sr-only z-[60] rounded-lg px-4 py-2 focus:not-sr-only focus:fixed focus:top-2 focus:left-2"
    >
        {{ __('site.skip_to_content') }}
    </a>

    <x-site.header />

    <main id="main" class="flex-1 pt-20" tabindex="-1">{{ $slot }}</main>

    <x-site.footer />
    <x-site.cookie-banner />
</body>
</html>
