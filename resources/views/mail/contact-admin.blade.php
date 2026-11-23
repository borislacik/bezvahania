<x-mail::message>
# {{ __('mail.contact_admin.heading') }}

<x-mail::panel>
**{{ __('contact.fields.name') }}:** {{ $contactMessage->name }}<br>
**{{ __('contact.fields.email') }}:** {{ $contactMessage->email }}<br>
**{{ __('contact.fields.phone') }}:** {{ $contactMessage->phone ?: '—' }}
</x-mail::panel>

{{ $contactMessage->message }}
</x-mail::message>
