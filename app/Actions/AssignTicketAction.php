<?php

namespace App\Actions;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;

class AssignTicketAction
{
    public function assign(Ticket $ticket, User $assignee): void
    {
        $ticket->update(['assignee_id' => $assignee->id]);

        if ($assignee->email) {
            $assignee->notify(new TicketAssignedNotification($ticket));
        }
    }

    public function unassign(Ticket $ticket): void
    {
        $ticket->update(['assignee_id' => null]);
    }
}
