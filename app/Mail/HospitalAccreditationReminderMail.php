<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HospitalAccreditationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $hospitalName;
    public string $programmeName;
    public string $expiryDateFormatted;
    public bool $isExpired;
    public ?string $senderName;
    public ?string $senderTitle;
    public ?string $senderPhone;
    public ?string $senderEmail;

    public function __construct(string $hospitalName, string $programmeName, string $expiryDate, ?\App\Models\User $sender = null)
    {
        $this->hospitalName = $hospitalName;
        $this->programmeName = $programmeName;
        $expiry = \Carbon\Carbon::parse($expiryDate);
        $this->expiryDateFormatted = $expiry->format('d M Y');
        $this->isExpired = $expiry->isPast();
        $this->senderName  = $sender?->name;
        $this->senderTitle = $sender?->signature_title;
        $this->senderPhone = $sender?->signature_phone;
        $this->senderEmail = $sender?->email;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), $this->senderName ?: config('mail.from.name')),
            replyTo: $this->senderEmail ? [new Address($this->senderEmail, $this->senderName)] : [],
            subject: ($this->isExpired ? 'Accreditation Expired' : 'Accreditation Renewal Reminder')
                . " — {$this->hospitalName} ({$this->programmeName})",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.hospital_accreditation_reminder');
    }
}
