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

            <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12">
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
                        <a href="#ako-prebieha" class="btn-light text-label-lg px-6 py-4">
                            <x-site.icon name="play_circle" class="text-primary size-5" />
                            {{ __('site.home.cta_how') }}
                        </a>
                    </div>

                    <dl class="mt-2 grid grid-cols-3 gap-4 pt-4">
                        @foreach (__('site.home.kpis') as $kpi)
                            <div class="flex flex-col-reverse">
                                <dt class="text-body-sm text-on-surface-variant">{{ $kpi['label'] }}</dt>
                                <dd
                                    @class (['font-heading text-headline-lg lg:text-headline-xl font-bold', 'text-primary' => $loop->index === 1, 'text-on-surface' => $loop->index !== 1])
                                >
                                    {{ $kpi['value'] }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="relative lg:col-span-5">
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
                            <div
                                class="from-on-surface/80 absolute inset-0 bg-gradient-to-t via-transparent to-transparent"
                            ></div>
                            <div class="text-on-primary absolute right-4 bottom-4 left-4">
                                <span
                                    class="bg-primary-container font-heading text-label-badge text-on-primary inline-flex rounded px-2 py-0.5 font-bold uppercase"
                                >
                                    {{ __('site.home.hero_badge') }}
                                </span>
                                <p class="font-heading text-headline-sm mt-1 font-semibold">{{ __('site.home.hero_caption') }}</p>
                                <p class="text-body-sm opacity-90">{{ __('site.home.hero_caption_sub') }}</p>
                            </div>
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

    {{-- FACT STRIP --}}
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
            <p class="flex items-center gap-2">
                <span
                    class="bg-primary-container inline-block size-2 animate-ping rounded-full motion-reduce:animate-none"
                ></span>
                <span
                    class="font-heading text-label-badge text-inverse-on-surface/90 font-bold tracking-wider uppercase"
                    >{{ __('site.home.fact_badge') }}</span
                >
            </p>
        </div>
    </section>

    {{-- PILLARS --}}
    <section class="section bg-surface" id="preco">
        <div class="container-page">
            <x-site.section-heading
                icon="shield"
                :eyebrow="__('site.home.pillars_eyebrow')"
                :title="__('site.home.pillars_title')"
                :lead="__('site.home.pillars_lead')"
            />
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach (__('site.home.pillars') as $pillar)
                    <article
                        class="card flex flex-col p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                    >
                        <span
                            @class ([
                                'mb-6 flex size-14 items-center justify-center rounded-xl shadow-sm',
                                'bg-primary-container text-on-primary-container' => $loop->index === 0,
                                'bg-surface-container text-primary' => $loop->index === 1,
                                'bg-secondary-container text-on-secondary-fixed' => $loop->index === 2,
                            ])
                        >
                            <x-site.icon :name="$pillar['icon']" class="size-8" />
                        </span>
                        <span
                            class="font-heading text-label-badge text-primary mb-2 font-bold tracking-wider uppercase"
                        >
                            {{ __('site.home.pillar_label', ['number' => sprintf('%02d', $loop->iteration)]) }}
                        </span>
                        <h3 class="text-headline-md text-on-surface mb-3 font-bold">{{ $pillar['title'] }}</h3>
                        <p class="text-body-md text-on-surface-variant mb-6">{{ $pillar['text'] }}</p>
                        <p class="font-heading text-label-md text-primary mt-auto flex items-center gap-2 pt-4 font-semibold">
                            <x-site.icon :name="$pillar['footer_icon']" class="size-[18px]" />
                            {{ $pillar['footer'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="section bg-surface-container-low" id="ako-prebieha">
        <div class="container-page grid grid-cols-1 items-center gap-12 lg:grid-cols-12">
            <div class="flex flex-col gap-6 lg:col-span-5">
                <span
                    class="font-heading text-label-md text-primary font-bold tracking-wider uppercase"
                    >{{ __('site.home.how_eyebrow') }}</span
                >
                <h2 class="text-headline-xl-mobile lg:text-headline-xl text-on-surface font-bold">
                    {{ __('site.home.how_title') }}
                </h2>
                <p class="text-body-md text-on-surface-variant">{{ __('site.home.how_lead') }}</p>
                <div class="bg-surface-container-lowest flex items-start gap-4 rounded-xl p-5 shadow-sm">
                    <x-site.icon name="family_restroom" class="text-primary size-7" />
                    <div>
                        <h3 class="text-headline-sm text-on-surface font-bold">{{ __('site.home.how_note_title') }}</h3>
                        <p class="text-body-sm text-on-surface-variant mt-1">{{ __('site.home.how_note_text') }}</p>
                    </div>
                </div>
            </div>
            <ol class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:col-span-7">
                @foreach (__('site.home.steps') as $step)
                    <li class="bg-surface-container-lowest flex flex-col rounded-xl p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <span
                                class="bg-primary-container text-on-primary font-heading text-label-lg flex size-8 items-center justify-center rounded-full font-bold"
                            >
                                {{ $loop->iteration }}
                            </span>
                            <x-site.icon :name="$step['icon']" class="text-on-surface-variant size-6" />
                        </div>
                        <h3 class="text-headline-sm text-on-surface mb-2 font-bold">{{ $step['title'] }}</h3>
                        <p class="text-body-sm text-on-surface-variant">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- WORKSHOPS --}}
    <section class="section bg-surface" id="workshopy">
        <div class="container-page">
            <x-site.section-heading
                icon="school"
                :eyebrow="__('site.home.workshops_eyebrow')"
                :title="__('site.home.workshops_title')"
                :lead="__('site.home.workshops_lead')"
            />
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <x-site.course-card :course="$course" :index="$loop->index" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- INSTRUCTOR --}}
    @if ($instructor)
        <section class="section bg-surface-container-low" id="lektor">
            <div class="container-page">
                <x-site.instructor :member="$instructor" />
            </div>
        </section>
    @endif

    {{-- TESTIMONIALS --}}
    @if ($testimonials->isNotEmpty())
        <section class="section bg-surface" id="skusenosti">
            <div class="container-page">
                <x-site.section-heading
                    icon="reviews"
                    :eyebrow="__('site.home.testimonials_eyebrow')"
                    :title="__('site.home.testimonials_title')"
                />
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @foreach ($testimonials as $testimonial)
                        <x-site.testimonial :testimonial="$testimonial" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="section bg-surface-container-low" id="otazky">
            <div class="container-page">
                <x-site.section-heading
                    icon="help"
                    :eyebrow="__('site.home.faq_eyebrow')"
                    :title="__('site.home.faq_title')"
                />
                <x-site.faq-list :faqs="$faqs" />
                <p class="mt-8 text-center">
                    <a
                        href="{{ route('faq') }}"
                        class="text-primary font-heading text-label-lg font-semibold underline"
                        >{{ __('site.home.faq_more') }}</a
                    >
                </p>
            </div>
        </section>
    @endif

    {{-- CONTACT CTA --}}
    <x-site.contact-cta />
</x-layouts.public>
