<?php

namespace App\Http\Requests;

use App\Enums\TimeSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')->where('is_active', true)],
            'date' => ['required', 'date_format:Y-m-d'],
            'time_slot' => ['required', Rule::enum(TimeSlot::class)],
            'contact_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9 +()\/-]{6,}$/'],
            'address' => ['required', 'string', 'max:255'],
            'participants' => ['required', 'integer', 'between:1,4'],
            'note' => ['nullable', 'string', 'max:2000'],
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
            'course_id' => __('booking.fields.course'),
            'date' => __('booking.fields.date'),
            'time_slot' => __('booking.fields.time_slot'),
            'contact_name' => __('booking.fields.contact_name'),
            'email' => __('booking.fields.email'),
            'phone' => __('booking.fields.phone'),
            'address' => __('booking.fields.address'),
            'participants' => __('booking.fields.participants'),
            'note' => __('booking.fields.note'),
            'consent' => __('booking.fields.consent_terms'),
        ];
    }

    /**
     * @return array{course_id: int, date: string, time_slot: string, contact_name: string, email: string, phone: string, address: string, participants: int, note: string|null}
     */
    public function bookingData(): array
    {
        return [
            'course_id' => $this->integer('course_id'),
            'date' => $this->string('date')->toString(),
            'time_slot' => $this->string('time_slot')->toString(),
            'contact_name' => $this->string('contact_name')->trim()->toString(),
            'email' => $this->string('email')->trim()->lower()->toString(),
            'phone' => $this->string('phone')->trim()->toString(),
            'address' => $this->string('address')->trim()->toString(),
            'participants' => $this->integer('participants'),
            'note' => $this->filled('note') ? $this->string('note')->trim()->toString() : null,
        ];
    }
}
