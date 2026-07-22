<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProgressReportAccessRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $requesterName;
    public string $sectionLabel;
    public string $periodMonthFormatted;
    public string $reviewUrl;
    public ?string $requesterEmail;

    public function __construct(string $requesterName, string $sectionLabel, string $periodMonthFormatted, string $reviewUrl, ?string $requesterEmail = null)
    {
        $this->requesterName = $requesterName;
        $this->sectionLabel = $sectionLabel;
        $this->periodMonthFormatted = $periodMonthFormatted;
        $this->reviewUrl = $reviewUrl;
        $this->requesterEmail = $requesterEmail;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: $this->requesterEmail ? [new Address($this->requesterEmail, $this->requesterName)] : [],
            subject: 'Progress Report — Edit Access Requested',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.progress_report_access_request');
    }
}
