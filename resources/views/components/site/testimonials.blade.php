{{--
    Testimonials. Mobile: all of them in a grid. Stacked (desktop column next to the FAQ): two at a time above each
    other; with more than two they change every few seconds (paused on hover/focus, not with prefers-reduced-motion)
    and the dots switch them by hand.
--}}
@props (['testimonials', 'stacked' => false])

@php ($pages = (int) ceil($testimonials->count() / 2))

<div
    @if ($stacked && $pages > 1)
        x-data="{
            page: 0,
            pages: {{ $pages }},
            paused: false,
            init() {
                // The fade-in only plays when the shown pair changes, not on the first display.
                this.$watch('page', () => this.$root.classList.add('testimonials--rotated'));
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                setInterval(() => { if (! this.paused) this.page = (this.page + 1) % this.pages; }, 8000);
            },
        }"
        @mouseenter="paused = true"
        @mouseleave="paused = false"
        @focusin="paused = true"
        @focusout="paused = false"
    @endif
    @class (['testimonials', 'testimonials--stacked' => $stacked])
    data-testid="testimonials"
>
    <div @class (['grid grid-cols-1 gap-6 md:grid-cols-2', 'lg:grid-cols-1' => $stacked])>
        @foreach ($testimonials as $testimonial)
            <div
                class="testimonials__item flex"
                data-testimonial-page="{{ intdiv($loop->index, 2) }}"
                @if ($stacked && $pages > 1)
                    :class="page !== {{ intdiv($loop->index, 2) }} && 'lg:hidden'"
                @endif
            >
                <x-site.testimonial :testimonial="$testimonial" class="w-full" />
            </div>
        @endforeach
    </div>

    @if ($stacked && $pages > 1)
        <div class="mt-6 hidden justify-center gap-2 lg:flex">
            @for ($i = 0; $i < $pages; $i++)
                <button
                    type="button"
                    class="h-2.5 rounded-full transition-all duration-300"
                    :class="page === {{ $i }} ? 'bg-primary-container w-6' : 'w-2.5 bg-slate-300 hover:bg-slate-400'"
                    @click="page = {{ $i }}"
                    :aria-current="page === {{ $i }}"
                    aria-label="{{ __('site.home.testimonials_page', ['page' => $i + 1]) }}"
                ></button>
            @endfor
        </div>
    @endif
</div>
