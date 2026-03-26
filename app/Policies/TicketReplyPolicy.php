<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;

class TicketReplyPolicy
{
    /**
     * Requester can reply to their own open tickets; staff can reply to any.
     */
    public function create(User $user, Ticket $ticket): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $ticket->requester_id === $user->id && ! $ticket->isResolved();
    }

    /**
     * Internal notes are only visible to staff. Public replies are visible to
     * anyone who can view the ticket.
     */
    public function view(User $user, TicketReply $reply): bool
    {
        if ($reply->is_internal) {
            return $user->isStaff();
        }

        return $user->isStaff() || $reply->ticket->requester_id === $user->id;
    }

    /** Only the author or an admin can delete a reply. */
    public function delete(User $user, TicketReply $reply): bool
    {
        return $user->isAdmin() || $reply->author_id === $user->id;
    }
}
