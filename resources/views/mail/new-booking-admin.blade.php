<x-mail::message>
# {{ __('mail.new_booking_admin.heading') }}

<x-mail::panel>
**{{ __('booking.summary.reference') }}:** {{ $booking->reference }}<br>
**{{ __('booking.summary.course') }}:** {{ $booking->course->name }}<br>
**{{ __('booking.summary.date') }}:** {{ $booking->date->translatedFormat('l j. n. Y') }}, {{ $booking->time_slot->range() }}<br>
**{{ __('booking.fields.contact_name') }}:** {{ $booking->contact_name }}<br>
**{{ __('booking.fields.phone') }}:** {{ $booking->phone }}<br>
**{{ __('booking.fields.email') }}:** {{ $booking->email }}<br>
**{{ __('booking.summary.address') }}:** {{ $booking->address }}<br>
**{{ __('booking.summary.participants') }}:** {{ $booking->participants }}
</x-mail::panel>

@if ($booking->note)
**{{ __('booking.fields.note') }}:**<br>
{{ $booking->note }}
@endif

<x-mail::button :url="config('app.admin_url')">
{{ __('mail.new_booking_admin.button') }}
</x-mail::button>
</x-mail::message>
