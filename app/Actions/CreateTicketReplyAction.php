<?php

namespace App\Actions;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\NewReplyNotification;

class CreateTicketReplyAction
{
    public function execute(Ticket $ticket, User $author, array $data): TicketReply
    {
        $reply = $ticket->replies()->create([
            'author_id' => $author->id,
            'body' => $data['body'],
            'is_internal' => $data['is_internal'] ?? false,
        ]);

        // Only send notification for non-internal replies
        if (! $reply->is_internal) {
            $this->notifyRelevantParties($ticket, $reply, $author);
        }

        return $reply;
    }

    private function notifyRelevantParties(Ticket $ticket, TicketReply $reply, User $author): void
    {
        // Notify the requester if the author is staff
        if ($author->isStaff() && $ticket->requester_id !== $author->id && $ticket->requester->email) {
            $ticket->requester->notify(new NewReplyNotification($ticket, $reply));
        }

        // Notify the assignee if it's not the one who replied
        if ($ticket->assignee && $ticket->assignee_id !== $author->id && $ticket->assignee->email) {
            $ticket->assignee->notify(new NewReplyNotification($ticket, $reply));
        }

        // Notify the requester's staff handler if the requester replied (let staff know)
        if (! $author->isStaff() && $ticket->assignee && $ticket->assignee->email) {
            $ticket->assignee->notify(new NewReplyNotification($ticket, $reply));
        }
    }
}
