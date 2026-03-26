<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
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
            ->subject("[#{$ticket->id}] New ticket: {$ticket->subject}")
            ->greeting('Hello!')
            ->line("A new support ticket has been created.")
            ->line("**#{$ticket->id} — {$ticket->subject}**")
            ->line("Priority: {$ticket->priority->label()}")
            ->action('View Ticket', $url)
            ->line('Thank you for reaching out. We will get back to you shortly.');
    }
}
