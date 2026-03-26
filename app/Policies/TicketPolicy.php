<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /** Any authenticated user can create tickets. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Staff can view all tickets; users can only view their own. */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isStaff() || $ticket->requester_id === $user->id;
    }

    /** Staff can view all tickets; users see only their own list. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Only staff can update ticket fields (subject, description, category). */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }

    /** Only staff can change ticket status. */
    public function updateStatus(User $user, Ticket $ticket): bool
    {
        return $user->isStaff() || $ticket->requester_id === $user->id;
    }

    /** Only staff can assign tickets. */
    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }

    /** Only admins can delete tickets. */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }
}
