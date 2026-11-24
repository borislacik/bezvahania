@props (['dark' => false])

<a href="{{ route('home') }}" class="group flex items-center gap-2.5" aria-label="{{ config('app.name') }}">
    {{-- Temporary logo (design/logo), the final version comes from the designer. --}}
    <img
        src="{{ Vite::asset('resources/images/logo.webp') }}"
        alt=""
        width="512"
        height="512"
        class="size-11 shrink-0"
        data-testid="site-logo"
    />
    <span class="flex flex-col">
        <span
            @class ([
                'font-heading text-headline-sm font-bold tracking-tight uppercase',
                'text-on-surface' => ! $dark,
                'text-inverse-on-surface' => $dark,
            ])
        >
            {{ config('app.name') }}
        </span>
        <span class="font-heading text-label-badge text-primary-container font-bold tracking-wider uppercase">
            {{ __('site.brand_tagline') }}
        </span>
    </span>
</a>
