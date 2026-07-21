<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LetterDispatchMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;
    public string $emailBody;
    public string $pdfContent;
    public string $pdfFilename;
    public ?string $senderName;
    public ?string $senderTitle;
    public ?string $senderPhone;
    public ?string $senderEmail;

    public function __construct(string $subject, string $body, string $pdfContent, string $pdfFilename, ?\App\Models\User $sender = null)
    {
        $this->emailSubject = $subject;
        $this->emailBody = $body;
        $this->pdfContent = $pdfContent;
        $this->pdfFilename = $pdfFilename;
        $this->senderName  = $sender?->name;
        $this->senderTitle = $sender?->signature_title;
        $this->senderPhone = $sender?->signature_phone;
        $this->senderEmail = $sender?->email;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                $this->senderName ?: config('mail.from.name'),
            ),
            replyTo: $this->senderEmail ? [new Address($this->senderEmail, $this->senderName)] : [],
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.letter_dispatch');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename)->withMime('application/pdf'),
        ];
    }
}
