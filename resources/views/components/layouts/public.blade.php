@props (['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ? $title.' | '.config('app.name') : config('app.name') }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}" />
    @endif
    @fonts
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface min-h-screen font-sans antialiased">
    <main id="main" class="mx-auto max-w-5xl px-4 py-16">{{ $slot }}</main>
</body>
</html>
