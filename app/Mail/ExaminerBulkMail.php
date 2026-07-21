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
    public ?string $senderName;
    public ?string $senderTitle;
    public ?string $senderPhone;
    public ?string $senderEmail;

    public function __construct(string $recipientName, string $subject, string $body, ?string $trackingToken = null)
    {
        $this->recipientName  = $recipientName;
        $this->emailSubject   = $subject;
        $this->trackingToken  = $trackingToken;
        // Replace [Name] placeholder with the actual recipient name
        $this->emailBody = str_replace('[Name]', $recipientName, $body);

        // Captured at construction (not send time) so a queued mailable
        // still carries the correct sender even after Auth::user() is gone.
        $sender = \Illuminate\Support\Facades\Auth::user();
        $this->senderName  = $sender?->name;
        $this->senderTitle = $sender?->signature_title;
        $this->senderPhone = $sender?->signature_phone;
        $this->senderEmail = $sender?->email;
    }

    public function envelope(): Envelope
    {
        // All outgoing mail still routes through the one authenticated SMTP
        // account (Gmail rejects/rewrites a From it doesn't own), but the
        // sending admin's name shows as the sender and replies go straight
        // to their own inbox — so recipients experience it as coming from
        // that staff member even though the technical envelope is shared.
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                $this->senderName ?: config('mail.from.name'),
            ),
            replyTo: $this->senderEmail
                ? [new \Illuminate\Mail\Mailables\Address($this->senderEmail, $this->senderName)]
                : [],
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
