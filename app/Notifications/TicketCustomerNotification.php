<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Sent to the customer: a receipt when the ticket is opened, and a copy of
 * every staff reply. Both carry the private link to follow the ticket.
 */
class TicketCustomerNotification extends Notification
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
        $mail = (new MailMessage)->greeting('Hi '.$ticket->name.',');

        if ($this->reply) {
            return $mail
                ->subject("[{$ticket->reference}] New reply: {$ticket->subject}")
                ->line('Our support team replied to your ticket:')
                ->line(Str::limit($this->reply->body, 1500))
                ->action('View the conversation', $ticket->publicUrl())
                ->line('Reply from the ticket page if you need anything else.');
        }

        return $mail
            ->subject("[{$ticket->reference}] We received your request")
            ->line("Thanks for contacting dPanel support. Your ticket reference is {$ticket->reference}.")
            ->line('We will reply by email. You can also follow the ticket and add details at any time:')
            ->action('Open your ticket', $ticket->publicUrl())
            ->line('Keep this link private: anyone with it can read the ticket.');
    }
}
