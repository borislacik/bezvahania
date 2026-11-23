<section class="section bg-surface">
    <div class="container-page">
        <div
            class="bg-inverse-surface text-inverse-on-surface grid gap-10 rounded-2xl p-8 shadow-xl lg:grid-cols-2 lg:p-12"
        >
            <div class="flex flex-col gap-4">
                <span
                    class="font-heading text-label-badge text-inverse-primary font-bold tracking-wider uppercase"
                    >{{ __('site.home.contact_eyebrow') }}</span
                >
                <h2 class="text-headline-xl-mobile lg:text-headline-xl font-bold">
                    {{ __('site.home.contact_title') }}
                </h2>
                <p class="text-body-md text-inverse-on-surface/80">{{ __('site.home.contact_text') }}</p>
                <ul class="mt-2 flex flex-col gap-3">
                    <li>
                        <a
                            href="tel:{{ preg_replace('/\s+/', '', $settings['contact_phone']) }}"
                            class="font-heading text-headline-sm flex items-center gap-3 font-bold hover:underline"
                        >
                            <x-site.icon name="call" class="text-inverse-primary size-6" />
                            {{ $settings['contact_phone'] }}
                        </a>
                    </li>
                    <li>
                        <a
                            href="mailto:{{ $settings['contact_email'] }}"
                            class="font-heading text-headline-sm flex items-center gap-3 font-bold hover:underline"
                        >
                            <x-site.icon name="mail" class="text-inverse-primary size-6" />
                            {{ $settings['contact_email'] }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="flex flex-col justify-between gap-6 rounded-xl bg-white/5 p-6">
                <div>
                    <h3 class="text-headline-sm mb-4 font-bold">{{ __('site.home.guarantee_title') }}</h3>
                    <ul class="flex flex-col gap-3">
                        @foreach (__('site.home.guarantees') as $guarantee)
                            <li class="text-body-md flex items-start gap-3">
                                <x-site.icon name="check" class="text-inverse-primary mt-0.5 size-5" />
                                {{ $guarantee }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <a href="{{ route('booking.create') }}" class="btn-primary">
                    {{ __('site.home.guarantee_cta') }}
                    <x-site.icon name="calendar_today" class="size-5" />
                </a>
            </div>
        </div>
    </div>
</section>
