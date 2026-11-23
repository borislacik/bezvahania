<x-mail::message>
# {{ __('mail.booking_cancelled.heading') }}

{{ __('mail.greeting', ['name' => $booking->contact_name]) }}

{{ __('mail.booking_cancelled.intro', ['reference' => $booking->reference, 'course' => $booking->course->name, 'date' => $booking->date->format('j. n. Y'), 'slot' => $booking->time_slot->range()]) }}

@if ($booking->cancellation_reason)
<x-mail::panel>
**{{ __('mail.booking_cancelled.reason') }}:** {{ $booking->cancellation_reason }}
</x-mail::panel>
@endif

{{ __('mail.booking_cancelled.rebook') }}

<x-mail::button :url="route('booking.create')">
{{ __('mail.booking_cancelled.button') }}
</x-mail::button>

{{ __('mail.signature') }}<br>
{{ config('app.name') }}
</x-mail::message>
