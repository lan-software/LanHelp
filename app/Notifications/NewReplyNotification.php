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
        public readonly string $notifyMode = 'link',
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

        $mail = (new MailMessage)
            ->subject("[#{$ticket->id}] New reply: {$ticket->subject}")
            ->greeting('Hello!');

        if ($this->notifyMode === 'content') {
            $mail->line("{$reply->author->displayName()} has replied to ticket **#{$ticket->id} — {$ticket->subject}**:")
                ->line($reply->body)
                ->action('View Conversation', $url);
        } else {
            $mail->line("{$reply->author->displayName()} has replied to ticket **#{$ticket->id} — {$ticket->subject}**.")
                ->action('View Conversation', $url);
        }

        return $mail;
    }
}
