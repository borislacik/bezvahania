<x-mail::message>
# {{ __('mail.booking_confirmed.heading') }}

{{ __('mail.greeting', ['name' => $booking->contact_name]) }}

{{ __('mail.booking_confirmed.intro') }}

<x-mail::panel>
**{{ __('booking.summary.reference') }}:** {{ $booking->reference }}<br>
**{{ __('booking.summary.course') }}:** {{ $booking->course->name }}<br>
**{{ __('booking.summary.date') }}:** {{ $booking->date->translatedFormat('l j. n. Y') }}, {{ $booking->time_slot->range() }}<br>
**{{ __('booking.summary.address') }}:** {{ $booking->address }}<br>
**{{ __('booking.summary.participants') }}:** {{ $booking->participants }}<br>
**{{ __('booking.summary.price') }}:** {{ \Illuminate\Support\Number::currency((float) $booking->price, 'EUR', 'sk') }}
</x-mail::panel>

{{ __('mail.booking_confirmed.payment') }}

{{ __('mail.booking_confirmed.change', ['phone' => \App\Models\Setting::get('contact_phone'), 'email' => \App\Models\Setting::get('contact_email')]) }}

{{ __('mail.signature') }}<br>
{{ config('app.name') }}
</x-mail::message>
