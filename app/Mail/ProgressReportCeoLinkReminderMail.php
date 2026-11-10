<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Reminder to the CEO that her monthly Secretariat report link has not been
// opened yet (see App\Console\Commands\SendCeoReportLinkReminders).
class ProgressReportCeoLinkReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $periodMonthFormatted;
    public string $reportUrl;
    public ?string $senderName;
    public ?string $senderTitle;
    public ?string $senderPhone;
    public ?string $senderEmail;

    public function __construct(string $periodMonthFormatted, string $reportUrl, ?\App\Models\User $sender = null)
    {
        $this->periodMonthFormatted = $periodMonthFormatted;
        $this->reportUrl = $reportUrl;
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
            subject: 'Reminder: Secretariat Report for ' . $this->periodMonthFormatted . ' is waiting for you',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.progress_report_ceo_link_reminder');
    }
}
