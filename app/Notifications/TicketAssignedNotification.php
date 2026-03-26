<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;
        $url = route('tickets.show', $ticket);

        return (new MailMessage)
            ->subject("[#{$ticket->id}] Ticket assigned to you: {$ticket->subject}")
            ->greeting('Hello!')
            ->line("You have been assigned to ticket **#{$ticket->id} — {$ticket->subject}**.")
            ->line("Priority: {$ticket->priority->label()}")
            ->action('View Ticket', $url);
    }
}
