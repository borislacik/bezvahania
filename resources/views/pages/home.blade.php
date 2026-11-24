<x-layouts.public :seo="$seo">
    {{-- HERO --}}
    <section
        class="from-surface-container-low via-surface to-surface-container-lowest relative w-full overflow-hidden bg-gradient-to-b pt-8 pb-16 lg:pt-14"
    >
        <div class="bg-primary/5 pointer-events-none absolute -top-20 -right-20 size-96 rounded-full blur-3xl"></div>
        <div
            class="bg-secondary-container/40 pointer-events-none absolute top-1/2 left-1/4 size-80 rounded-full blur-3xl"
        ></div>

        <div class="container-page relative">
            <ul class="flex flex-wrap items-center gap-2 pb-6">
                <li class="eyebrow bg-surface-container-lowest text-primary shadow-sm">
                    <x-site.icon name="verified" class="size-4" />
                    {{ __('site.home.pills.practice') }}
                </li>
                <li class="eyebrow bg-surface-container-lowest text-on-surface-variant shadow-sm">
                    <x-site.icon name="home_pin" class="text-primary size-4" />
                    {{ __('site.home.pills.home') }}
                </li>
                <li class="eyebrow bg-surface-container-lowest text-on-surface-variant shadow-sm">
                    <x-site.icon name="groups" class="text-tertiary size-4" />
                    {{ __('site.home.pills.group') }}
                </li>
            </ul>

            {{-- Desktop: the top of the main heading lines up with the top of the image (lg:mt-7 = eyebrow + gap). --}}
            <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12 lg:items-start">
                <div class="flex flex-col gap-6 lg:col-span-7">
                    <div class="flex flex-col gap-3">
                        <span
                            class="font-heading text-label-md text-primary font-bold tracking-widest uppercase"
                            >{{ __('site.home.eyebrow') }}</span
                        >
                        <h1 class="text-display-hero-mobile lg:text-display-hero text-on-surface font-bold">
                            {{ __('site.home.title_start') }}
                            <span class="text-primary relative inline-block">
                                {{ __('site.home.title_highlight') }}
                                <svg class="text-primary/30 absolute -bottom-1.5 left-0 w-full" height="8" preserveAspectRatio="none" viewBox="0 0 100 8" aria-hidden="true">
                                    <path d="M0,5 Q50,0 100,5" fill="none" stroke="currentColor" stroke-width="4"></path>
                                </svg>
                            </span>
                            {{ __('site.home.title_end') }}
                        </h1>
                    </div>
                    <p class="text-body-lg text-on-surface-variant max-w-2xl">{{ __('site.home.lead') }}</p>

                    <div class="flex flex-col items-stretch gap-4 pt-2 sm:flex-row sm:items-center">
                        <a href="{{ route('booking.create') }}" class="btn-primary text-headline-sm px-7 py-4">
                            {{ __('site.home.cta_book') }}
                            <x-site.icon name="calendar_month" class="size-5" />
                        </a>
                    </div>
                </div>

                <div class="relative lg:col-span-5 lg:mt-7">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <div class="bg-surface-container-highest relative overflow-hidden rounded-2xl shadow-xl">
                            <img
                                src="{{ Vite::asset('resources/images/hero-cpr.webp') }}"
                                alt="{{ __('site.home.hero_image_alt') }}"
                                width="1376"
                                height="768"
                                fetchpriority="high"
                                class="h-80 w-full object-cover lg:h-[420px]"
                            />
                            {{-- Decorative, intentionally quiet: red is reserved for the booking calls to action. --}}
                            <span
                                class="font-heading text-label-badge text-on-surface absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-full bg-white/85 px-2.5 py-1 font-bold uppercase shadow-sm backdrop-blur-md"
                                data-testid="hero-badge"
                            >
                                <span class="relative flex size-2" aria-hidden="true">
                                    <span
                                        class="bg-primary-container absolute inline-flex size-full animate-ping rounded-full opacity-60 motion-reduce:hidden"
                                    ></span>
                                    <span class="bg-primary-container relative inline-flex size-2 rounded-full"></span>
                                </span>
                                {{ __('site.home.hero_badge') }}
                            </span>
                        </div>
                        <div
                            class="bg-surface-container-lowest absolute -bottom-6 -left-6 hidden items-center gap-3 rounded-xl p-4 shadow-xl sm:flex"
                        >
                            <span
                                class="bg-surface-container text-primary flex size-12 items-center justify-center rounded-lg"
                            >
                                <x-site.icon name="ecg_heart-filled" class="size-7" />
                            </span>
                            <span class="flex flex-col">
                                <span
                                    class="font-heading text-label-lg text-on-surface font-bold"
                                    >{{ __('site.home.hero_card_title') }}</span
                                >
                                <span
                                    class="text-body-sm text-on-surface-variant"
                                    >{{ __('site.home.hero_card_text') }}</span
                                >
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FACT STRIP + PILLARS + WORKSHOPS: on desktop they move slowly while the pillars add up (components/site/pillars-scrolly) --}}
    <x-site.pillars-scrolly>
        {{-- FACT STRIP: part of the slow block, so it does not jump away when the pillars slow down --}}
        <x-slot:before>
            <section class="bg-inverse-surface text-inverse-on-surface w-full py-6">
                <div class="container-page flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="flex items-center gap-3">
                        <span
                            class="bg-primary-container text-on-primary flex size-10 shrink-0 items-center justify-center rounded-full"
                        >
                            <x-site.icon name="timer" class="size-[22px]" />
                        </span>
                        <div>
                            <p class="font-heading text-headline-sm font-bold">{{ __('site.home.fact_title') }}</p>
                            <p class="text-body-sm text-inverse-on-surface/80">{{ __('site.home.fact_text') }}</p>
                        </div>
                    </div>
                    <p class="ecg-badge relative flex h-14 w-64 shrink-0 items-center justify-center">
                        {{-- Animated ECG trace behind the text (static with prefers-reduced-motion). --}}
                        <svg
                            class="ecg-line pointer-events-none absolute inset-0 h-full w-full"
                            viewBox="0 0 240 40"
                            preserveAspectRatio="none"
                            aria-hidden="true"
                        >
                            <path
                                class="ecg-line__base"
                                d="M0 29.5 C5.7 28.0 8.2 26.5 10.7 26.5 C12.6 26.5 14.2 27.7 15.7 29.5 L23.2 29.5 L25.9 33.0 L28.5 3.8 L31.4 36.7 L34.0 29.5 L44.1 29.5 C46.8 27.6 49.3 25.1 51.8 25.1 C54.0 25.1 55.5 27.1 57.0 29.5 L183 29.5 C185.7 28.0 188.2 26.5 190.7 26.5 C192.6 26.5 194.2 27.7 195.7 29.5 L203.2 29.5 L205.9 33.0 L208.5 3.8 L211.4 36.7 L214.0 29.5 L224.1 29.5 C226.8 27.6 229.3 25.1 231.8 25.1 C234.0 25.1 235.5 27.1 237.0 29.5 L240 29.5"
                            />
                            <path
                                class="ecg-line__pulse"
                                pathLength="100"
                                d="M0 29.5 C5.7 28.0 8.2 26.5 10.7 26.5 C12.6 26.5 14.2 27.7 15.7 29.5 L23.2 29.5 L25.9 33.0 L28.5 3.8 L31.4 36.7 L34.0 29.5 L44.1 29.5 C46.8 27.6 49.3 25.1 51.8 25.1 C54.0 25.1 55.5 27.1 57.0 29.5 L183 29.5 C185.7 28.0 188.2 26.5 190.7 26.5 C192.6 26.5 194.2 27.7 195.7 29.5 L203.2 29.5 L205.9 33.0 L208.5 3.8 L211.4 36.7 L214.0 29.5 L224.1 29.5 C226.8 27.6 229.3 25.1 231.8 25.1 C234.0 25.1 235.5 27.1 237.0 29.5 L240 29.5"
                            />
                        </svg>
                        <span
                            class="font-heading text-label-badge text-inverse-on-surface bg-inverse-surface relative rounded px-1.5 font-bold tracking-wider whitespace-nowrap uppercase"
                            >{{ __('site.home.fact_badge') }}</span
                        >
                    </p>
                </div>
            </section>
        </x-slot:before>

        <section class="section bg-surface" id="{{ \App\Support\HomeSections::WORKSHOPS }}">
            <div class="container-page">
                <x-site.section-heading
                    icon="school"
                    :eyebrow="__('site.home.workshops_eyebrow')"
                    :title="__('site.home.workshops_title')"
                    :lead="__('site.home.workshops_lead')"
                />
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    @foreach ($courses as $course)
                        <x-site.course-card :course="$course" />
                    @endforeach
                </div>
            </div>
        </section>
    </x-site.pillars-scrolly>

    {{-- INSTRUCTOR --}}
    @if ($instructor)
        <section class="section bg-surface-container-low" id="{{ \App\Support\HomeSections::INSTRUCTOR }}">
            <div class="container-page">
                <x-site.instructor :member="$instructor" />
            </div>
        </section>
    @endif

    {{--
        FAQ + TESTIMONIALS. Desktop: FAQ on the left (2/3), testimonials on the right (1/3, two at a time, rotating
        when there are more). Mobile: as before – testimonials first, then the FAQ.
    --}}
    @if ($faqs->isNotEmpty() || $testimonials->isNotEmpty())
        @php ($both = $faqs->isNotEmpty() && $testimonials->isNotEmpty())
        <section class="section bg-surface">
            <div @class (['container-page grid grid-cols-1 gap-x-12', 'lg:grid-cols-3' => $both])>
                @if ($testimonials->isNotEmpty())
                    <div id="skusenosti" @class (['order-1', 'lg:col-start-3 lg:row-start-1' => $both])>
                        <x-site.section-heading
                            icon="reviews"
                            :eyebrow="__('site.home.testimonials_eyebrow')"
                            :title="__('site.home.testimonials_title')"
                            :align="$both ? 'responsive' : 'center'"
                            :compact="$both"
                        />
                    </div>
                    <div @class (['order-2 mb-16', 'lg:col-start-3 lg:row-start-2 lg:mb-0' => $both])>
                        <x-site.testimonials :testimonials="$testimonials" :stacked="$both" />
                    </div>
                @endif

                @if ($faqs->isNotEmpty())
                    <div
                        id="{{ \App\Support\HomeSections::FAQ }}"
                        @class (['order-3', 'lg:col-span-2 lg:col-start-1 lg:row-start-1 lg:self-end' => $both])
                    >
                        <x-site.section-heading
                            icon="help"
                            :eyebrow="__('site.home.faq_eyebrow')"
                            :title="__('site.home.faq_title')"
                            :align="$both ? 'responsive' : 'center'"
                        />
                    </div>
                    <div @class (['order-4', 'lg:col-span-2 lg:col-start-1 lg:row-start-2' => $both])>
                        <x-site.faq-list :faqs="$faqs" @class(['lg:max-w-none' => $both]) />
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- CONTACT --}}
    <x-site.contact-cta :id="\App\Support\HomeSections::CONTACT" />
</x-layouts.public>
