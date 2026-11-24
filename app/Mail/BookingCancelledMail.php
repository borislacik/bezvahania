<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        // Sent from the automatic address (MAIL_FROM_ADDRESS); customer replies go to the contact e-mail.
        return new Envelope(replyTo: [new Address(Setting::get('contact_email'), (string) config('app.name'))], subject: __('mail.booking_cancelled.subject', ['reference' => $this->booking->reference]));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.booking-cancelled');
    }
}
