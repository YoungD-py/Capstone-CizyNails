<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingPaymentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Booking Payment - Cizy Nails',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-payment-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
