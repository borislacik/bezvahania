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
            <form
                id="kontaktny-formular"
                x-data
                x-init="$el.querySelector('.field-error, [role=status]') && $el.scrollIntoView({ block: 'center' })"
                method="POST"
                action="{{ route('contact.store') }}"
                novalidate
                class="bg-surface-container-lowest text-on-surface flex scroll-mt-28 flex-col gap-4 rounded-xl p-6 shadow-lg"
                data-testid="contact-cta-form"
            >
                @csrf
                <h3 class="text-headline-sm font-bold">{{ __('contact.quick.title') }}</h3>

                @if (session('status'))
                    <div class="bg-success-container text-success flex items-center gap-3 rounded-xl p-4" role="status">
                        <x-site.icon name="check_circle" class="size-6" />
                        <p class="text-body-md font-semibold">{{ session('status') }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-site.field
                        id-prefix="cta"
                        name="name"
                        :label="__('contact.fields.name')"
                        autocomplete="name"
                        required
                    />
                    <x-site.field
                        id-prefix="cta"
                        name="email"
                        type="email"
                        :label="__('contact.fields.email')"
                        autocomplete="email"
                        required
                    />
                </div>
                <x-site.field
                    id-prefix="cta"
                    name="message"
                    type="textarea"
                    rows="4"
                    :label="__('contact.fields.message')"
                    :placeholder="__('contact.quick.placeholder')"
                    required
                />

                {{-- Honeypot --}}
                <div class="hidden" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off" />
                </div>

                <div>
                    <label class="text-body-sm text-on-surface flex items-start gap-3">
                        <input
                            type="checkbox"
                            name="consent"
                            value="1"
                            class="accent-primary-container mt-0.5 size-5 shrink-0"
                            @checked (old('consent'))
                            required
                        />
                        <span>
                            {!! __('contact.fields.consent', [
                                'privacy' => '<a class="text-primary underline" href="'.route('legal.privacy').'" target="_blank">'.e(__('contact.fields.consent_privacy')).'</a>',
                            ]) !!}
                        </span>
                    </label>
                    @error ('consent')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full">
                    {{ __('contact.submit') }}
                    <x-site.icon name="arrow_forward" class="size-5" />
                </button>
            </form>
        </div>
    </div>
</section>
