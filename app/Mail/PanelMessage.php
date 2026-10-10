<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A plain message written by staff in the admin panel: a reply to a
 * comment, review, or donation, or a fresh email from the compose screen.
 */
class PanelMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $body,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
        public ?string $recipientName = null,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = config('site.support_email');

        return new Envelope(
            subject: $this->subjectLine,
            replyTo: $replyTo ? [new Address($replyTo, config('site.name'))] : [],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.panel-message');
    }
}
