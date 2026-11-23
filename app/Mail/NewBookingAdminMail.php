<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewBookingAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->booking->email, $this->booking->contact_name)],
            subject: __('mail.new_booking_admin.subject', [
                'date' => $this->booking->date->format('j. n. Y'),
                'slot' => $this->booking->time_slot->label(),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.new-booking-admin');
    }
}
