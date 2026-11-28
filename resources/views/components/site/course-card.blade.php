@props (['course', 'headingLevel' => 'h3'])

<article
    class="card flex flex-col overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
    data-testid="course-card"
>
    <div class="bg-surface-container-high relative h-56 w-full overflow-hidden">
        @if ($course->imageUrl())
            <img
                src="{{ $course->imageUrl() }}"
                alt="{{ $course->name }}"
                width="1376"
                height="768"
                loading="lazy"
                decoding="async"
                class="size-full object-cover transition-transform duration-500 hover:scale-105"
            />
        @else
            <div
                class="from-surface-container via-surface-container-low to-surface-container-highest flex size-full flex-col items-center justify-center bg-gradient-to-br p-6 text-center"
            >
                <span
                    class="bg-surface-container-lowest text-primary mb-3 flex size-16 items-center justify-center rounded-2xl shadow-sm"
                >
                    <x-site.icon name="medical_services" class="size-9" />
                </span>
            </div>
        @endif

        @if ($course->badge)
            {{-- Quiet frosted label like "Živý nácvik" in the hero: red stays reserved for the booking buttons. --}}
            <span
                class="font-heading text-label-badge text-on-surface absolute top-4 left-4 rounded-full bg-white/70 px-2.5 py-1 font-bold uppercase shadow-sm backdrop-blur-md"
                data-testid="course-badge"
            >
                {{ $course->badge }}
            </span>
        @endif

        <span
            class="font-heading text-label-badge text-on-surface absolute right-3 bottom-3 rounded-lg bg-white/70 px-3 py-1 font-bold shadow-sm backdrop-blur-md"
        >
            {{ $course->duration_label }}
        </span>
    </div>

    <div class="flex flex-grow flex-col p-6">
        <{{ $headingLevel }} class="text-headline-md text-on-surface mb-2 font-bold">
            <a href="{{ route('workshops.show', $course) }}" class="hover:text-primary">{{ $course->name }}</a>
        </{{ $headingLevel }}>
        <p class="text-body-sm text-on-surface-variant mb-4">{{ $course->short_description }}</p>

        <dl class="border-surface-container text-body-sm mt-auto space-y-2 border-y py-4">
            <div class="flex items-center justify-between gap-4">
                <dt class="text-on-surface-variant flex items-center gap-1.5">
                    <x-site.icon name="group" class="size-[18px]" />
                    {{ __('site.common.group') }}:
                </dt>
                <dd class="text-on-surface text-right font-semibold">{{ __('site.common.group_value') }}</dd>
            </div>
            @if ($course->equipment)
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-on-surface-variant flex shrink-0 items-center gap-1.5">
                        <x-site.icon name="inventory_2" class="size-[18px]" />
                        {{ __('site.common.equipment') }}:
                    </dt>
                    <dd class="text-on-surface text-right font-semibold">{{ $course->equipment }}</dd>
                </div>
            @endif
            <div class="flex items-center justify-between gap-4">
                <dt class="text-on-surface-variant flex items-center gap-1.5">
                    <x-site.icon name="payments" class="size-[18px]" />
                    {{ __('site.common.price') }}:
                </dt>
                <dd class="font-heading text-headline-sm text-primary text-right font-bold">
                    {{ Number::currency((float) $course->price, 'EUR', 'sk', 0) }}
                </dd>
            </div>
        </dl>

        @if ($bundle->isBundled($course))
            <a
                href="{{ route('home') }}#balik"
                class="text-body-sm text-primary mt-3 flex items-center gap-1.5 font-semibold hover:underline"
                data-testid="course-bundle-note"
            >
                <x-site.icon name="verified" class="size-[18px] shrink-0" />
                {{ __('site.bundle.card', ['price' => Number::currency($bundle->bundlePrice(), 'EUR', 'sk', 0)]) }}
            </a>
        @endif

        <a
            href="{{ route('booking.create', ['workshop' => $course->slug]) }}#rezervacia"
            class="btn-primary mt-6 w-full"
        >
            {{ __('site.common.book_course') }}
            <x-site.icon name="arrow_forward" class="size-[18px]" />
        </a>
    </div>
</article>
