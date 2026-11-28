<x-mail::message>
# {{ __('mail.booking_confirmed.heading') }}

{{ __('mail.greeting', ['name' => $booking->contact_name]) }}

{{ __('mail.booking_confirmed.intro') }}

<x-mail::panel>
**{{ __('booking.summary.reference') }}:** {{ $booking->reference }}<br>
**{{ __('booking.summary.course') }}:** {{ $booking->course->name }}<br>
**{{ __('booking.summary.date') }}:** {{ $booking->date->translatedFormat('l j. n. Y') }}, {{ $booking->time_slot->range() }}<br>
**{{ __('booking.summary.address') }}:** {{ $booking->address }}<br>
@if ($booking->billing_address)
**{{ __('booking.summary.billing_address') }}:** {!! preg_replace('/\R/', '<br>', e($booking->billing_address)) !!}<br>
@endif
**{{ __('booking.summary.participants') }}:** {{ $booking->participants }}<br>
**{{ __('booking.summary.price') }}:** {{ \Illuminate\Support\Number::currency((float) $booking->price, 'EUR', 'sk') }}@if ($booking->bundle_booking_id) ({{ __('booking.summary.bundle_price') }})@endif

</x-mail::panel>

{{ __('mail.booking_confirmed.payment') }}

{{ __('mail.booking_confirmed.change', ['email' => \App\Models\Setting::get('contact_email')]) }}

{{ __('mail.booking_confirmed.cancellation_terms', ['url' => route('legal.cancellation'), 'phone' => \App\Models\Setting::get('contact_phone')]) }}

@php($bundleOfferCourse = $booking->bundleCounterpart() === null ? $bundle->partnerCourse($booking->course) : null)
@if ($bundleOfferCourse)
{{ __('mail.booking_confirmed.bundle_offer', [
    'course' => $bundleOfferCourse->name,
    'price' => \Illuminate\Support\Number::currency((float) $bundle->discountedPrice($bundleOfferCourse), 'EUR', 'sk', 0),
    'total' => \Illuminate\Support\Number::currency($bundle->bundlePrice(), 'EUR', 'sk', 0),
    'url' => route('booking.create', ['workshop' => $bundleOfferCourse->slug]),
]) }}
@endif

{{ __('mail.signature') }}<br>
{{ config('app.name') }}
</x-mail::message>
