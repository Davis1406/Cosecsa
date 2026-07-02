<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExaminerBulkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $emailSubject;
    public string $emailBody;   // HTML, may contain [Name] placeholder
    public ?string $trackingToken;

    public function __construct(string $recipientName, string $subject, string $body, ?string $trackingToken = null)
    {
        $this->recipientName  = $recipientName;
        $this->emailSubject   = $subject;
        $this->trackingToken  = $trackingToken;
        // Replace [Name] placeholder with the actual recipient name
        $this->emailBody = str_replace('[Name]', $recipientName, $body);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                config('mail.from.name'),
            ),
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.examiner_bulk',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
