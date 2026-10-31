<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** The password reset email. Always sent from the college's communications mailbox, whatever MAIL_FROM_ADDRESS says. */
class ForgetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public const FROM_ADDRESS = 'communications@cosecsa.org';
    public const FROM_NAME = 'COSECSA';

    /**
     * @param  array<int, array{label: string, roles: string, url: string, name: ?string}>  $accounts  one reset link per account
     */
    public function __construct(public array $accounts, public int $minutes)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(self::FROM_ADDRESS, self::FROM_NAME),
            subject: 'Reset your COSECSA MIS password',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.forget', with: ['senderEmail' => self::FROM_ADDRESS]);
    }
}
