@props (['eyebrow' => null, 'icon' => null, 'title', 'lead' => null, 'level' => 'h2', 'align' => 'center'])

<div
    @class ([
        'mb-12 flex max-w-3xl flex-col gap-3',
        'mx-auto items-center text-center' => $align === 'center',
    ])
>
    @if ($eyebrow)
        <span class="eyebrow bg-surface-container text-primary">
            @if ($icon)
                <x-site.icon :name="$icon" class="size-4" />
            @endif
            {{ $eyebrow }}
        </span>
    @endif
    <{{ $level }}
        class="text-headline-xl-mobile lg:text-headline-xl text-on-surface font-bold"
        >{{ $title }}</{{ $level }}
    >
    @if ($lead)
        <p class="text-body-lg text-on-surface-variant">{{ $lead }}</p>
    @endif
</div>
