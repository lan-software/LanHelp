<?php

namespace App\Actions;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Support\Carbon;

class UpdateTicketStatusAction
{
    public function execute(Ticket $ticket, TicketStatus $newStatus, User $actor): void
    {
        $oldStatus = $ticket->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $attributes = ['status' => $newStatus];

        if ($newStatus === TicketStatus::Resolved) {
            $attributes['resolved_at'] = Carbon::now();
        } elseif ($newStatus === TicketStatus::Closed) {
            $attributes['closed_at'] = Carbon::now();
        } elseif (in_array($newStatus, TicketStatus::active())) {
            // Re-opening: clear resolved/closed timestamps
            $attributes['resolved_at'] = null;
            $attributes['closed_at'] = null;
        }

        $ticket->update($attributes);

        // Notify the requester about the status change
        if ($ticket->requester_id !== $actor->id && $ticket->requester->email) {
            $ticket->requester->notify(new TicketStatusChangedNotification($ticket, $oldStatus));
        }
    }
}
