<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Email ServiceKU (Resend)',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.system-test',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
