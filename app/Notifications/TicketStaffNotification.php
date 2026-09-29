<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Sent to the support inbox when a ticket is opened or the customer replies.
 */
class TicketStaffNotification extends Notification
{
    public function __construct(
        public SupportTicket $ticket,
        public ?TicketReply $reply = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;

        return (new MailMessage)
            ->subject(($this->reply ? 'Customer reply' : 'New ticket')." [{$ticket->reference}] {$ticket->subject}")
            ->replyTo($ticket->email, $ticket->name)
            ->line("From: {$ticket->name} <{$ticket->email}>")
            ->line('Category: '.$ticket->categoryLabel().' · Priority: '.ucfirst($ticket->priority))
            ->line(Str::limit($this->reply?->body ?? $ticket->message, 1500))
            ->action('Open in the help desk', route('admin.tickets.show', $ticket));
    }
}
