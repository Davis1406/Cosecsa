<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProgressReportCeoShareMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $periodMonthFormatted;
    public string $docxFilename;
    public string $docxBinary;
    public ?string $senderName;
    public ?string $senderTitle;
    public ?string $senderPhone;
    public ?string $senderEmail;

    public function __construct(string $periodMonthFormatted, string $docxFilename, string $docxBinary, ?\App\Models\User $sender = null)
    {
        $this->periodMonthFormatted = $periodMonthFormatted;
        $this->docxFilename = $docxFilename;
        $this->docxBinary = $docxBinary;
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
            subject: 'Consolidated Secretariat Report — ' . $this->periodMonthFormatted,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.progress_report_ceo_share');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->docxBinary, $this->docxFilename)
                ->withMime('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ];
    }
}
