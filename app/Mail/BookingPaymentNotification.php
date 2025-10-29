<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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
        $attachments = [];
        
        if ($this->booking->payment_proof_path && Storage::disk('public')->exists($this->booking->payment_proof_path)) {
            $attachments[] = Attachment::fromStorage('public', $this->booking->payment_proof_path)
                ->as('payment_proof_' . $this->booking->id . '.jpg');
        }
        
        return $attachments;
    }
}
