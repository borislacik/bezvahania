{{--
    Pillars with a "slow scroll" on desktop (lg+):
    the section above ($before, the dark fact strip), the pillars section and the start of the following section ($slot)
    stick when the top of the pillars reaches 30 % of the screen height and moves up at ~1/3 speed while the pillars light up one by one
    (none at first, the first one shortly after the slowing starts); then normal scrolling continues.
    Mobile: no slowing, pillars fade in and out as they enter and leave the screen.
    Without JS or with prefers-reduced-motion: everything static and visible.
--}}
@php ($pillars = __('site.home.pillars'))

<div
    class="pillars-scrolly relative"
    x-data="{
        active: -1,
        enabled: false,
        distance: 0,
        drift: 0,
        init() {
            this.$root.classList.add('pillars-js');
            const media = window.matchMedia('(min-width: 64rem) and (prefers-reduced-motion: no-preference)');
            const setup = () => {
                this.enabled = media.matches;
                this.distance = this.enabled ? Math.round(window.innerHeight * 1.2) : 0;
                this.drift = Math.round(this.distance / 3);
                // The block pins (and the pillars start adding up) when the top of the pillars reaches 30 % of the
                // screen; the section above them ($before) is part of the block, so it moves away just as slowly.
                const pinAt = Math.max(80, Math.round(window.innerHeight * 0.3)) - this.$refs.pillars.offsetTop;
                this.$refs.inner.style.top = this.enabled ? pinAt + 'px' : '';
                // The spacer gives the sticky block the pin distance; the negative margin removes the gap
                // left by the drift, so the page continues seamlessly afterwards.
                this.$refs.spacer.style.height = this.enabled ? this.distance + 'px' : '0px';
                this.$root.style.marginBottom = this.enabled ? -this.drift + 'px' : '';
                this.$root.classList.toggle('pillars-slow', this.enabled);
                this.update();
            };
            media.addEventListener('change', setup);
            window.addEventListener('resize', setup);
            window.addEventListener('scroll', () => this.update(), { passive: true });

            // Mobile: pillars fade in and out as they enter and leave the screen.
            const reveal = new IntersectionObserver((entries) => {
                entries.forEach((entry) => entry.target.classList.toggle('is-visible', entry.isIntersecting));
            }, { rootMargin: '-10% 0px -10% 0px' });
            this.$root.querySelectorAll('[data-pillar]').forEach((item) => reveal.observe(item));

            setup();

            // Anchors into the slowed section (menu item of the following section): the browser would compute
            // the target while the block is pinned, so scroll to where the section ends up after the pin.
            const target = this.$refs.inner.querySelector('section[id]:not(#preco)');
            if (! target) return;
            document.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (! this.enabled || ! link) return;
                const url = new URL(link.href, window.location.href);
                if (url.pathname !== window.location.pathname || url.hash !== '#' + target.id) return;
                event.preventDefault();
                history.pushState(null, '', url.hash);
                this.scrollToTarget(target, 'smooth');
            });
            if (this.enabled && window.location.hash === '#' + target.id) {
                const jump = () => this.scrollToTarget(target, 'instant');
                jump();
                window.addEventListener('load', jump, { once: true });
            }
        },
        scrollToTarget(target, behavior) {
            const padding = parseFloat(getComputedStyle(document.documentElement).scrollPaddingTop) || 0;
            const rootTop = this.$root.getBoundingClientRect().top + window.scrollY;
            window.scrollTo({ top: rootTop + target.offsetTop + this.distance - this.drift - padding, behavior });
        },
        update() {
            const inner = this.$refs.inner;
            if (! this.enabled) {
                inner.style.transform = '';
                this.active = {{ count($pillars) - 1 }};
                return;
            }
            const top = parseFloat(getComputedStyle(inner).top) || 0;
            const progress = Math.min(1, Math.max(0, (top - this.$root.getBoundingClientRect().top) / this.distance));
            inner.style.transform = `translate3d(0, ${-progress * this.drift}px, 0)`;
            // Nothing is lit when the block arrives; the pillars then light up in equal steps, the first one
            // after 10 % of the slowed part, and the last one stays lit for the final stretch.
            const steps = {{ count($pillars) }};
            const lead = 0.1;
            const step = (1 - lead) / (steps + 0.5);
            this.active = progress < lead ? -1 : Math.min(steps - 1, Math.floor((progress - lead) / step));
        },
    }"
    data-testid="pillars"
>
    <div x-ref="inner" class="pillars-scrolly__inner will-change-transform lg:sticky lg:top-20">
        {{ $before ?? '' }}
        <section x-ref="pillars" class="bg-surface-container-low py-14 lg:py-16" id="preco">
            <div class="container-page grid grid-cols-1 gap-10 lg:grid-cols-12 lg:items-center lg:gap-16">
                {{-- Left: heading and compact panel --}}
                <div class="flex flex-col gap-4 lg:col-span-5">
                    <span class="eyebrow bg-surface-container-lowest text-primary">
                        <x-site.icon name="shield" class="size-4" />
                        {{ __('site.home.pillars_eyebrow') }}
                    </span>
                    <h2 class="text-headline-xl-mobile lg:text-headline-xl text-on-surface font-bold">
                        {{ __('site.home.pillars_title') }}
                    </h2>
                    <p class="text-body-lg text-on-surface-variant">{{ __('site.home.pillars_lead') }}</p>

                    {{-- Progress panel: each pillar adds its icon above its part of the line. --}}
                    <div
                        class="bg-inverse-surface relative mt-2 hidden overflow-hidden rounded-xl px-5 pt-3 pb-4 shadow-lg lg:block"
                        aria-hidden="true"
                    >
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($pillars as $pillar)
                                <div class="flex flex-col items-center gap-2.5">
                                    <span
                                        class="pillar-add bg-primary-container text-on-primary flex size-9 items-center justify-center rounded-lg shadow-md"
                                        :class="active >= {{ $loop->index }} && 'is-added'"
                                    >
                                        <x-site.icon :name="$pillar['icon']" class="size-5" />
                                    </span>
                                    <span class="h-1 w-full overflow-hidden rounded-full bg-white/15">
                                        <span
                                            class="bg-primary-container block h-full origin-left transition-transform duration-500 ease-out motion-reduce:transition-none"
                                            :class="active >= {{ $loop->index }} ? 'scale-x-100' : 'scale-x-0'"
                                        ></span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Right: pillars close together --}}
                <ol class="flex flex-col gap-7 lg:col-span-7">
                    @foreach ($pillars as $pillar)
                        <li
                            data-pillar="{{ $loop->index }}"
                            class="pillar-item flex flex-col gap-2"
                            :class="active >= {{ $loop->index }} ? 'is-added' : 'is-future'"
                        >
                            <h3 class="text-headline-md text-on-surface flex items-center gap-3 font-bold">
                                <span
                                    class="bg-primary-container text-on-primary flex size-10 shrink-0 items-center justify-center rounded-lg lg:hidden"
                                >
                                    <x-site.icon :name="$pillar['icon']" class="size-6" />
                                </span>
                                {{ $pillar['title'] }}
                            </h3>
                            <p class="text-body-md text-on-surface-variant max-w-xl">{{ $pillar['text'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Start of the following section moves together with the pillars --}}
        {{ $slot }}
    </div>
    <div x-ref="spacer" aria-hidden="true"></div>
</div>
