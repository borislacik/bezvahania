<x-layouts.public :seo="$seo">
    <x-site.page-header :title="__('contact.title')" :lead="__('contact.lead')" />

    <section class="bg-surface pb-16 lg:pb-24">
        <div class="container-page grid grid-cols-1 gap-10 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <div class="bg-inverse-surface text-inverse-on-surface flex flex-col gap-6 rounded-2xl p-8 shadow-xl">
                    <h2 class="text-headline-md font-bold">{{ __('contact.details') }}</h2>
                    <dl class="space-y-5">
                        <div class="flex items-start gap-3">
                            <x-site.icon name="call" class="text-inverse-primary size-6" />
                            <div>
                                <dt class="text-body-sm text-inverse-on-surface/70">{{ __('contact.phone') }}</dt>
                                <dd>
                                    <a
                                        class="font-heading text-headline-sm font-bold hover:underline"
                                        href="tel:{{ preg_replace('/\s+/', '', $settings['contact_phone']) }}"
                                        >{{ $settings['contact_phone'] }}</a
                                    >
                                </dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <x-site.icon name="mail" class="text-inverse-primary size-6" />
                            <div>
                                <dt class="text-body-sm text-inverse-on-surface/70">{{ __('contact.email') }}</dt>
                                <dd>
                                    <a
                                        class="font-heading text-headline-sm font-bold hover:underline"
                                        href="mailto:{{ $settings['contact_email'] }}"
                                        >{{ $settings['contact_email'] }}</a
                                    >
                                </dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <x-site.icon name="location_on" class="text-inverse-primary size-6" />
                            <div>
                                <dt class="text-body-sm text-inverse-on-surface/70">{{ __('contact.area') }}</dt>
                                <dd class="font-heading text-headline-sm font-bold">{{ __('site.service_area') }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="lg:col-span-7">
                <form
                    id="kontaktny-formular"
                    x-data
                    x-init="$el.querySelector('.field-error, [role=status]') && $el.scrollIntoView({ block: 'center' })"
                    method="POST"
                    action="{{ route('contact.store') }}"
                    novalidate
                    class="card flex scroll-mt-28 flex-col gap-4 p-6 lg:p-8"
                    data-testid="contact-form"
                >
                    @csrf
                    <h2 class="text-headline-md text-on-surface font-bold">{{ __('contact.form_title') }}</h2>

                    @if (session('status'))
                        <div
                            class="bg-success-container text-success flex items-center gap-3 rounded-xl p-4"
                            role="status"
                        >
                            <x-site.icon name="check_circle" class="size-6" />
                            <p class="text-body-md font-semibold">{{ session('status') }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-site.field name="name" :label="__('contact.fields.name')" autocomplete="name" required />
                        <x-site.field
                            name="email"
                            type="email"
                            :label="__('contact.fields.email')"
                            autocomplete="email"
                            required
                        />
                    </div>
                    <x-site.field name="phone" type="tel" :label="__('contact.fields.phone')" autocomplete="tel" />
                    <x-site.field
                        name="message"
                        type="textarea"
                        :label="__('contact.fields.message')"
                        rows="6"
                        required
                    />

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

                    <button type="submit" class="btn-primary mt-2 self-start">
                        {{ __('contact.submit') }}
                        <x-site.icon name="arrow_forward" class="size-5" />
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.public>
