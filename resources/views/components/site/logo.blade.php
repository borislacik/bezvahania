@props (['dark' => false])

<a href="{{ route('home') }}" class="group flex items-center gap-2" aria-label="{{ config('app.name') }}">
    <span
        class="bg-primary-container text-on-primary-container flex size-10 items-center justify-center rounded-lg shadow-sm"
    >
        <x-site.icon name="medical_services" class="size-6" />
    </span>
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
