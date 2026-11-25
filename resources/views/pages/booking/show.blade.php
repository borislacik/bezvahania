<x-layouts.public :seo="$seo">
    <section class="section bg-surface-container-low">
        <div class="container-page max-w-3xl">
            <div
                class="card flex flex-col items-center gap-4 p-8 text-center lg:p-12"
                data-testid="booking-confirmation"
            >
                <span class="bg-success-container text-success flex size-16 items-center justify-center rounded-full">
                    <x-site.icon name="verified" class="size-9" />
                </span>
                <h1 class="text-headline-xl-mobile lg:text-headline-xl text-on-surface font-bold">
                    {{ __('booking.confirmation.title') }}
                </h1>
                <p class="text-body-lg text-on-surface-variant">{{ __('booking.confirmation.lead', ['email' => $booking->email]) }}</p>

                <dl class="bg-surface-container-low text-body-md mt-4 w-full space-y-2 rounded-xl p-6 text-left">
                    <div class="flex justify-between gap-4">
                        <dt class="text-on-surface-variant">{{ __('booking.summary.reference') }}</dt>
                        <dd class="text-on-surface font-bold">{{ $booking->reference }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-on-surface-variant">{{ __('booking.summary.course') }}</dt>
                        <dd class="text-on-surface text-right font-bold">{{ $booking->course->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-on-surface-variant">{{ __('booking.summary.date') }}</dt>
                        <dd class="text-on-surface text-right font-bold">
                            {{ ucfirst($booking->date->translatedFormat('l j. F Y')) }}, {{ $booking->time_slot->range() }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-on-surface-variant">{{ __('booking.summary.address') }}</dt>
                        <dd class="text-on-surface text-right font-bold">{{ $booking->address }}</dd>
                    </div>
                    @if ($booking->billing_address)
                        <div class="flex justify-between gap-4" data-testid="billing-summary">
                            <dt class="text-on-surface-variant">{{ __('booking.summary.billing_address') }}</dt>
                            <dd class="text-on-surface text-right font-bold">
                                {!! nl2br(e($booking->billing_address)) !!}
                            </dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-on-surface-variant">{{ __('booking.summary.participants') }}</dt>
                        <dd class="text-on-surface font-bold">{{ $booking->participants }}</dd>
                    </div>
                    <div class="border-surface-container flex justify-between gap-4 border-t pt-2">
                        <dt class="text-on-surface-variant">{{ __('booking.summary.price') }}</dt>
                        <dd class="font-heading text-headline-sm text-primary font-bold">
                            {{ Number::currency((float) $booking->price, 'EUR', 'sk', 0) }}
                        </dd>
                    </div>
                </dl>

                <p class="text-body-md text-on-surface-variant">{{ __('booking.confirmation.next') }}</p>
                <p class="text-body-sm text-on-surface-variant">
                    {!! __('booking.confirmation.change', [
                        'email' => '<a class="text-primary underline" href="mailto:'.e($settings['contact_email']).'">'.e($settings['contact_email']).'</a>',
                        'terms' => '<a class="text-primary underline" href="'.route('legal.cancellation').'">'.e(__('booking.confirmation.change_terms')).'</a>',
                    ]) !!}
                </p>
                <a href="{{ route('home') }}" class="btn-dark mt-2">{{ __('booking.confirmation.home') }}</a>
            </div>
        </div>
    </section>
</x-layouts.public>
