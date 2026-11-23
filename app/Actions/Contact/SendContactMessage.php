<?php

namespace App\Actions\Contact;

use App\Mail\ContactMessageAdminMail;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

/**
 * Stores a message from the contact form and forwards it to the operator.
 */
final class SendContactMessage
{
    /**
     * @param  array{name: string, email: string, phone?: string|null, message: string}  $data
     */
    public function handle(array $data): ContactMessage
    {
        $message = ContactMessage::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'consent_at' => now(),
        ]);

        Mail::to(Setting::get('notification_email'))->queue(new ContactMessageAdminMail($message));

        return $message;
    }
}
