@props (['testimonial'])

<figure class="card flex flex-col gap-4 p-6">
    <div class="flex items-center gap-3">
        <span
            class="bg-primary-container text-on-primary font-heading text-headline-sm flex size-11 items-center justify-center rounded-full font-bold"
            aria-hidden="true"
        >
            {{ mb_substr($testimonial->author, 0, 1) }}
        </span>
        <div>
            <figcaption class="font-heading text-label-lg text-on-surface font-bold">
                {{ $testimonial->author }}
            </figcaption>
            <div
                class="text-primary-container flex"
                role="img"
                aria-label="{{ __('site.home.rating', ['rating' => $testimonial->rating]) }}"
            >
                @for ($i = 0; $i < $testimonial->rating; $i++)
                    <x-site.icon name="star-filled" class="size-4" />
                @endfor
            </div>
        </div>
    </div>
    <blockquote class="text-body-md text-on-surface-variant italic">„{{ $testimonial->text }}“</blockquote>
    @if ($testimonial->context)
        <p class="font-heading text-label-md text-on-surface-variant mt-auto">{{ $testimonial->context }}</p>
    @endif
</figure>
