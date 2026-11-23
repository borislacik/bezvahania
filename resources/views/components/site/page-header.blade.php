@props (['title', 'lead' => null, 'eyebrow' => null])

<section class="from-surface-container-low to-surface bg-gradient-to-b pt-12 pb-10 lg:pt-16">
    <div class="container-page">
        @if ($eyebrow)
            <span class="eyebrow bg-surface-container text-primary mb-3">{{ $eyebrow }}</span>
        @endif
        <h1 class="text-display-hero-mobile lg:text-headline-xl text-on-surface max-w-3xl font-bold">{{ $title }}</h1>
        @if ($lead)
            <p class="text-body-lg text-on-surface-variant mt-4 max-w-2xl">{{ $lead }}</p>
        @endif
    </div>
</section>
