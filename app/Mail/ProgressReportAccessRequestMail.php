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

    public function __construct(string $requesterName, string $sectionLabel, string $periodMonthFormatted, string $reviewUrl)
    {
        $this->requesterName = $requesterName;
        $this->sectionLabel = $sectionLabel;
        $this->periodMonthFormatted = $periodMonthFormatted;
        $this->reviewUrl = $reviewUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Progress Report — Edit Access Requested',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.progress_report_access_request');
    }
}
