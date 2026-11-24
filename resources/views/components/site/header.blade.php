@php
    // One-page navigation: every menu item scrolls to a section of the home page (also from other pages).
    $links = collect(\App\Support\HomeSections::MENU)->map(fn (string $section, string $label) => [
        'section' => $section,
        'href' => \App\Support\HomeSections::url($section),
        'label' => __("site.nav.{$label}"),
    ]);
    $onHome = request()->routeIs('home');
@endphp

<header
    x-data="{
        menu: false,
        active: null,
        init() {
            if (! @js($onHome)) return;
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => { if (entry.isIntersecting) this.active = entry.target.id });
            }, { rootMargin: '-45% 0px -50% 0px' });
            @js($links->pluck('section')).forEach((id) => {
                const section = document.getElementById(id);
                if (section) observer.observe(section);
            });
        },
    }"
    @keydown.escape.window="menu = false"
    class="bg-surface/90 fixed inset-x-0 top-0 z-50 shadow-[0_1px_8px_rgba(0,0,0,0.04)] backdrop-blur-xl"
>
    <div class="container-page flex h-20 items-center justify-between gap-4">
        <x-site.logo />

        <nav aria-label="{{ __('site.nav.label') }}" class="hidden items-center gap-6 lg:flex">
            @foreach ($links as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="font-heading text-label-lg transition-colors"
                    :class="active === '{{ $link['section'] }}' ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface'"
                    data-nav="{{ $link['section'] }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            <a href="{{ route('booking.create') }}" class="btn-primary text-label-lg hidden px-4 py-2.5 sm:inline-flex">
                <x-site.icon name="calendar_month" class="size-5" />
                {{ __('site.nav.book') }}
            </a>
            <button
                type="button"
                class="text-on-surface hover:bg-surface-container inline-flex size-11 items-center justify-center rounded-lg lg:hidden"
                @click="menu = !menu"
                :aria-expanded="menu.toString()"
                aria-controls="mobile-menu"
            >
                <span class="sr-only" x-text="menu ? @js(__('site.nav.close_menu')) : @js(__('site.nav.open_menu'))">
                    {{ __('site.nav.open_menu') }}
                </span>
                <span x-show="!menu"><x-site.icon name="menu" class="size-7" /></span>
                <span x-show="menu" x-cloak><x-site.icon name="close" class="size-7" /></span>
            </button>
        </div>
    </div>

    <nav
        id="mobile-menu"
        x-show="menu"
        x-collapse
        x-cloak
        aria-label="{{ __('site.nav.label') }}"
        class="bg-surface-container-lowest border-surface-container border-t lg:hidden"
    >
        <div class="container-page flex flex-col gap-1 py-4">
            @foreach ($links as $link)
                <a
                    href="{{ $link['href'] }}"
                    @click="menu = false"
                    class="font-heading text-headline-sm rounded-lg px-3 py-3"
                    :class="active === '{{ $link['section'] }}' ? 'text-primary bg-surface-container-low' : 'text-on-surface hover:bg-surface-container-low'"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a href="{{ route('booking.create') }}" class="btn-primary mt-2">
                <x-site.icon name="calendar_month" class="size-5" />
                {{ __('site.nav.book') }}
            </a>
        </div>
    </nav>
</header>
