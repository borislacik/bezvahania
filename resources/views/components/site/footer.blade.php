<footer class="bg-inverse-surface text-inverse-on-surface">
    <div class="container-page grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-4">
        <div class="flex flex-col gap-4 lg:col-span-1">
            <x-site.logo dark />
            <p class="text-body-sm text-inverse-on-surface/80">{{ __('site.footer.about') }}</p>
        </div>

        <nav aria-label="{{ __('site.footer.links') }}">
            <h2 class="text-headline-sm mb-4 font-bold">{{ __('site.footer.links') }}</h2>
            <ul class="text-body-sm text-inverse-on-surface/80 space-y-2">
                <li>
                    <a
                        class="hover:text-inverse-on-surface"
                        href="{{ route('workshops.index') }}"
                        >{{ __('site.nav.workshops') }}</a
                    >
                </li>
                <li>
                    <a
                        class="hover:text-inverse-on-surface"
                        href="{{ route('booking.create') }}"
                        >{{ __('site.nav.book') }}</a
                    >
                </li>
                <li>
                    <a class="hover:text-inverse-on-surface" href="{{ route('about') }}">{{ __('site.nav.about') }}</a>
                </li>
                <li><a class="hover:text-inverse-on-surface" href="{{ route('faq') }}">{{ __('site.nav.faq') }}</a></li>
            </ul>
        </nav>

        <nav aria-label="{{ __('site.footer.legal') }}">
            <h2 class="text-headline-sm mb-4 font-bold">{{ __('site.footer.legal') }}</h2>
            <ul class="text-body-sm text-inverse-on-surface/80 space-y-2">
                <li>
                    <a
                        class="hover:text-inverse-on-surface"
                        href="{{ route('legal.terms') }}"
                        >{{ __('site.pages.terms') }}</a
                    >
                </li>
                <li>
                    <a
                        class="hover:text-inverse-on-surface"
                        href="{{ route('legal.privacy') }}"
                        >{{ __('site.pages.privacy') }}</a
                    >
                </li>
                <li>
                    <a
                        class="hover:text-inverse-on-surface"
                        href="{{ route('legal.cookies') }}"
                        >{{ __('site.pages.cookies') }}</a
                    >
                </li>
                <li>
                    <button
                        type="button"
                        class="hover:text-inverse-on-surface underline-offset-2 hover:underline"
                        @click="$dispatch('open-cookie-settings')"
                        x-data
                    >
                        {{ __('site.footer.cookie_settings') }}
                    </button>
                </li>
            </ul>
        </nav>

        <div>
            <h2 class="text-headline-sm mb-4 font-bold">{{ __('site.footer.contact') }}</h2>
            <ul class="text-body-sm text-inverse-on-surface/80 space-y-3">
                <li class="flex items-center gap-2">
                    <x-site.icon name="call" class="text-inverse-primary size-5" />
                    <a
                        class="hover:text-inverse-on-surface"
                        href="tel:{{ preg_replace('/\s+/', '', $settings['contact_phone']) }}"
                        >{{ $settings['contact_phone'] }}</a
                    >
                </li>
                <li class="flex items-center gap-2">
                    <x-site.icon name="mail" class="text-inverse-primary size-5" />
                    <a
                        class="hover:text-inverse-on-surface"
                        href="mailto:{{ $settings['contact_email'] }}"
                        >{{ $settings['contact_email'] }}</a
                    >
                </li>
                <li class="flex items-center gap-2">
                    <x-site.icon name="location_on" class="text-inverse-primary size-5" />
                    <span>{{ __('site.service_area') }}</span>
                </li>
            </ul>
            @if ($settings['operator_name'])
                <p class="text-body-sm text-inverse-on-surface/60 mt-6">
                    {{ __('site.footer.operator') }}: {{ $settings['operator_name'] }}
                    @if ($settings['operator_ico'])
                        <br
                        />{{ __('site.footer.ico', ['ico' => $settings['operator_ico']]) }}
                    @endif
                    @if ($settings['operator_address'])
                        <br
                        />{{ $settings['operator_address'] }}
                    @endif
                </p>
            @endif
        </div>
    </div>
    <div class="border-t border-white/10">
        <p class="container-page text-body-sm text-inverse-on-surface/60 py-6">
            {{ __('site.footer.rights', ['year' => now()->year, 'name' => config('app.name')]) }}
        </p>
    </div>
</footer>
