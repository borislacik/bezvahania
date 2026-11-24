{{-- align: center (default) | responsive (centered on mobile, left from lg); compact: smaller title from lg (narrow columns). --}}
@props (['eyebrow' => null, 'icon' => null, 'title', 'lead' => null, 'level' => 'h2', 'align' => 'center', 'compact' => false])

<div
    @class ([
        'mb-12 flex max-w-3xl flex-col gap-3',
        'mx-auto items-center text-center' => $align !== 'left',
        'lg:mx-0 lg:items-start lg:text-left' => $align === 'responsive',
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
        @class (['text-headline-xl-mobile text-on-surface font-bold', 'lg:text-headline-xl' => ! $compact, 'lg:text-headline-lg' => $compact])
        >{{ $title }}</{{ $level }}
    >
    @if ($lead)
        <p class="text-body-lg text-on-surface-variant">{{ $lead }}</p>
    @endif
</div>
