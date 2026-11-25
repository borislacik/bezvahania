@props (['member', 'headingLevel' => 'h2'])

<div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12">
    <div class="relative order-2 lg:order-1 lg:col-span-5">
        <div class="relative mx-auto max-w-md">
            {{-- Photos change every few seconds (paused on hover/focus, not with reduced motion); dots switch them. --}}
            @php ($photos = $member->photoUrls() ?: [Vite::asset('resources/images/instructor.webp')])
            <div
                class="bg-surface relative h-[460px] overflow-hidden rounded-2xl shadow-xl"
                data-testid="instructor-photos"
                @if (count($photos) > 1)
                    x-data="{
                        current: 0,
                        count: {{ count($photos) }},
                        paused: false,
                        init() {
                            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                            setInterval(() => { if (! this.paused) this.current = (this.current + 1) % this.count; }, 6000);
                        },
                    }"
                    @mouseenter="paused = true"
                    @mouseleave="paused = false"
                    @focusin="paused = true"
                    @focusout="paused = false"
                @endif
            >
                @foreach ($photos as $photo)
                    <img
                        src="{{ $photo }}"
                        alt="{{ $loop->first ? $member->name : __('site.home.instructor_photo_alt', ['name' => $member->name, 'number' => $loop->iteration]) }}"
                        width="1024"
                        height="1024"
                        loading="lazy"
                        decoding="async"
                        data-photo="{{ $loop->index }}"
                        @class ([
                            'absolute inset-0 size-full object-cover transition-opacity duration-700 motion-reduce:transition-none',
                            'opacity-0' => ! $loop->first,
                        ])
                        @if (count($photos) > 1)
                            :class="current === {{ $loop->index }} ? 'opacity-100' : 'opacity-0'"
                            :aria-hidden="current !== {{ $loop->index }}"
                        @endif
                    />
                @endforeach

                @if (count($photos) > 1)
                    <div
                        class="absolute bottom-4 left-4 flex gap-1.5 rounded-full bg-white/55 px-2.5 py-2 shadow-sm backdrop-blur-md"
                    >
                        @foreach ($photos as $photo)
                            <button
                                type="button"
                                class="h-2 rounded-full transition-all duration-300"
                                :class="current === {{ $loop->index }} ? 'bg-primary-container w-5' : 'w-2 bg-slate-400 hover:bg-slate-500'"
                                @click="current = {{ $loop->index }}"
                                :aria-current="current === {{ $loop->index }}"
                                aria-label="{{ __('site.home.instructor_photo_show', ['number' => $loop->iteration]) }}"
                            ></button>
                        @endforeach
                    </div>
                @endif
            </div>
            <div
                class="text-inverse-surface absolute -right-2 -bottom-6 flex items-center gap-3 rounded-xl bg-white/55 p-4 shadow-xl backdrop-blur-md sm:right-6"
                data-testid="instructor-badge"
            >
                <span class="bg-primary text-on-primary flex size-10 items-center justify-center rounded-lg">
                    <x-site.icon name="ambulance" class="size-6" />
                </span>
                <span class="flex flex-col">
                    <span class="font-heading text-label-badge text-primary font-bold uppercase">
                        {{ __('site.home.instructor_badge_label') }}
                    </span>
                    <span
                        class="font-heading text-headline-sm font-semibold"
                        >{{ __('site.home.instructor_badge_value') }}</span
                    >
                </span>
            </div>
        </div>
    </div>

    <div class="order-1 flex flex-col gap-6 lg:order-2 lg:col-span-7">
        <div class="flex flex-col gap-2">
            <span class="eyebrow bg-surface-container text-primary">
                <x-site.icon name="badge" class="size-4" />
                {{ __('site.home.instructor_eyebrow') }}
            </span>
            <{{ $headingLevel }}
                class="text-headline-xl-mobile lg:text-headline-xl text-on-surface font-bold"
                >{{ $member->name }}</{{ $headingLevel }}
            >
            <span class="font-heading text-headline-sm text-primary font-semibold">{{ $member->role }}</span>
        </div>

        @if ($member->quote)
            <blockquote
                class="bg-surface-container-lowest text-body-lg text-on-surface relative rounded-xl p-6 italic shadow-sm"
            >
                <x-site.icon
                    name="format_quote"
                    class="text-primary/30 pointer-events-none absolute top-2 right-4 size-12"
                />
                „{{ $member->quote }}“
            </blockquote>
        @endif

        @if ($member->bio)
            <div class="text-body-md text-on-surface-variant space-y-3">{!! nl2br(e($member->bio)) !!}</div>
        @endif

        <ul class="grid grid-cols-1 gap-4 pt-2 sm:grid-cols-3">
            @foreach (__('site.home.instructor_credentials') as $credential)
                <li class="bg-surface-container-lowest flex items-start gap-3 rounded-xl p-4 shadow-sm">
                    <x-site.icon :name="$credential['icon']" class="text-primary size-[22px]" />
                    <span>
                        <span
                            class="font-heading text-label-lg text-on-surface block font-bold"
                            >{{ $credential['title'] }}</span
                        >
                        <span class="text-body-sm text-on-surface-variant">{{ $credential['text'] }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
