<x-layouts.public :seo="$seo">
    <section class="from-surface-container-low to-surface bg-gradient-to-b pt-10 pb-16 lg:pt-14">
        <div class="container-page">
            <a
                href="{{ \App\Support\HomeSections::url(\App\Support\HomeSections::WORKSHOPS) }}"
                class="text-label-lg text-on-surface-variant hover:text-primary font-heading mb-6 inline-flex items-center gap-1"
            >
                <x-site.icon name="chevron_left" class="size-5" />
                {{ __('site.common.all_workshops') }}
            </a>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
                <div class="flex flex-col gap-6 lg:col-span-7">
                    @if ($course->badge)
                        <span class="eyebrow bg-primary-container text-on-primary">
                            <x-site.icon name="star" class="size-3.5" />
                            {{ $course->badge }}
                        </span>
                    @endif
                    <h1 class="text-display-hero-mobile lg:text-headline-xl text-on-surface font-bold">
                        {{ $course->name }}
                    </h1>
                    <p class="text-body-lg text-on-surface-variant">{{ $course->short_description }}</p>

                    @if ($course->imageUrl())
                        <img
                            src="{{ $course->imageUrl() }}"
                            alt="{{ $course->name }}"
                            width="1376"
                            height="768"
                            class="h-72 w-full rounded-2xl object-cover shadow-xl lg:h-96"
                        />
                    @endif

                    @if ($course->description)
                        <div>
                            <h2 class="text-headline-lg text-on-surface mb-3 font-bold">
                                {{ __('site.workshops.about') }}
                            </h2>
                            <div class="prose-legal">{!! $course->description !!}</div>
                        </div>
                    @endif
                </div>

                <aside class="lg:col-span-5">
                    <div class="card sticky top-28 flex flex-col gap-5 p-6">
                        <h2 class="text-headline-md text-on-surface font-bold">{{ __('site.workshops.practical') }}</h2>
                        <dl class="text-body-md space-y-3">
                            @foreach ([
                                ['icon' => 'schedule', 'label' => __('site.common.duration'), 'value' => $course->duration_label],
                                ['icon' => 'group', 'label' => __('site.common.group'), 'value' => __('site.common.group_value')],
                                ['icon' => 'inventory_2', 'label' => __('site.common.equipment'), 'value' => $course->equipment],
                                ['icon' => 'home_pin', 'label' => __('site.workshops.place'), 'value' => __('site.workshops.place_value')],
                                ['icon' => 'payments', 'label' => __('site.workshops.payment'), 'value' => __('site.workshops.payment_value')],
                                ['icon' => 'workspace_premium', 'label' => __('site.workshops.certificate'), 'value' => __('site.workshops.certificate_value')],
                            ] as $row)
                                @if ($row['value'])
                                    <div class="flex items-start gap-3">
                                        <x-site.icon :name="$row['icon']" class="text-primary mt-0.5 size-5" />
                                        <div>
                                            <dt class="text-body-sm text-on-surface-variant">{{ $row['label'] }}</dt>
                                            <dd class="text-on-surface font-semibold">{{ $row['value'] }}</dd>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                        <div class="border-surface-container flex items-center justify-between border-t pt-4">
                            <span class="text-on-surface-variant">{{ __('site.common.price') }}</span>
                            <span
                                class="font-heading text-headline-lg text-primary font-bold"
                                >{{ Number::currency((float) $course->price, 'EUR', 'sk', 0) }}</span
                            >
                        </div>
                        <a
                            href="{{ route('booking.create', ['workshop' => $course->slug]) }}#rezervacia"
                            class="btn-primary w-full"
                        >
                            {{ __('site.common.book_course') }}
                            <x-site.icon name="arrow_forward" class="size-5" />
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if ($others->isNotEmpty())
        <section class="section bg-surface-container-low">
            <div class="container-page">
                <x-site.section-heading :title="__('site.workshops.other')" />
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    @foreach ($others as $other)
                        <x-site.course-card :course="$other" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
