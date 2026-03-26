<?php

namespace App\Notifications;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly TicketStatus $oldStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;
        $url = route('tickets.show', $ticket);

        return (new MailMessage)
            ->subject("[#{$ticket->id}] Status updated: {$ticket->subject}")
            ->greeting('Hello!')
            ->line("The status of ticket **#{$ticket->id} — {$ticket->subject}** has changed.")
            ->line("**{$this->oldStatus->label()} → {$ticket->status->label()}**")
            ->action('View Ticket', $url);
    }
}
