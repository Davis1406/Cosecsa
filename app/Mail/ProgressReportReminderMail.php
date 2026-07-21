<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProgressReportReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $dueDateFormatted;
    public ?string $senderName;
    public ?string $senderTitle;
    public ?string $senderPhone;
    public ?string $senderEmail;

    public function __construct(string $dueDateFormatted, ?\App\Models\User $sender = null)
    {
        $this->dueDateFormatted = $dueDateFormatted;
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
            subject: 'Monthly Progress Report — Reminder',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.progress_report_reminder');
    }
}
