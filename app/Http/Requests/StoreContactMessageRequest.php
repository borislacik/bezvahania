<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9 +()\/-]{6,}$/'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'consent' => ['accepted'],
            // Honeypot: real visitors never fill this hidden field.
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('contact.fields.name'),
            'email' => __('contact.fields.email'),
            'phone' => __('contact.fields.phone'),
            'message' => __('contact.fields.message'),
            'consent' => __('contact.fields.consent_privacy'),
        ];
    }

    /**
     * @return array{name: string, email: string, phone: string|null, message: string}
     */
    public function messageData(): array
    {
        return [
            'name' => $this->string('name')->trim()->toString(),
            'email' => $this->string('email')->trim()->lower()->toString(),
            'phone' => $this->filled('phone') ? $this->string('phone')->trim()->toString() : null,
            'message' => $this->string('message')->trim()->toString(),
        ];
    }
}
