<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly TicketReply $reply,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;
        $reply = $this->reply;
        $url = route('tickets.show', $ticket);

        return (new MailMessage)
            ->subject("[#{$ticket->id}] New reply: {$ticket->subject}")
            ->greeting('Hello!')
            ->line("{$reply->author->displayName()} has replied to ticket **#{$ticket->id} — {$ticket->subject}**.")
            ->action('View Conversation', $url);
    }
}
