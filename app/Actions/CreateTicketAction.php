<?php

namespace App\Actions;

use App\Enums\TicketPriority;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Support\Facades\Notification;

class CreateTicketAction
{
    public function execute(User $requester, array $data): Ticket
    {
        $ticket = Ticket::create([
            'requester_id' => $requester->id,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => TicketPriority::tryFrom($data['priority'] ?? '') ?? TicketPriority::Normal,
            'category' => $data['category'] ?? null,
            'context_snapshot' => $data['context_snapshot'] ?? null,
        ]);

        $requester->notify(new TicketCreatedNotification($ticket));

        $staffEmail = config('lanhelp.staff_email');
        if ($staffEmail) {
            Notification::route('mail', $staffEmail)
                ->notify(new TicketCreatedNotification($ticket));
        }

        return $ticket;
    }
}
