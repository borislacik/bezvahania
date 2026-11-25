<footer class="bg-inverse-surface text-inverse-on-surface">
    <div class="container-page grid gap-10 py-14 md:grid-cols-3">
        <div class="flex flex-col gap-4">
            <x-site.logo dark />
            <p class="text-body-sm text-inverse-on-surface/80">{{ __('site.footer.about') }}</p>
        </div>

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
                        data-cookie-settings
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
            </ul>
            {{-- Business details (admin: Kontakt a prevádzkovateľ); icons instead of labels. --}}
            @if ($settings['operator_name'])
                <ul class="text-body-sm text-inverse-on-surface/80 mt-3 space-y-3">
                    <li class="flex items-start gap-2">
                        <x-site.icon name="badge" class="text-inverse-primary mt-0.5 size-5 shrink-0" />
                        <span>
                            <span class="text-inverse-on-surface/90 block">{{ $settings['operator_name'] }}</span>
                            @if ($settings['operator_ico'])
                                {{ __('site.footer.ico', ['ico' => $settings['operator_ico']]) }}
                            @endif
                            @if ($settings['operator_dic'])
                                <span aria-hidden="true">·</span>
                                {{ __('site.footer.dic', ['dic' => $settings['operator_dic']]) }}
                            @endif
                            @if ($settings['operator_vat_note'])
                                <span class="block">{{ $settings['operator_vat_note'] }}</span>
                            @endif
                        </span>
                    </li>
                    @if ($settings['operator_address'])
                        <li class="flex items-start gap-2">
                            <x-site.icon name="home_pin" class="text-inverse-primary mt-0.5 size-5 shrink-0" />
                            <span>{{ $settings['operator_address'] }}</span>
                        </li>
                    @endif
                </ul>
            @endif
        </div>
    </div>
    <div class="border-t border-white/10">
        <p class="container-page text-body-sm text-inverse-on-surface/60 py-6">
            {{ __('site.footer.rights', ['year' => now()->year, 'name' => config('app.name')]) }}
        </p>
    </div>
</footer>
