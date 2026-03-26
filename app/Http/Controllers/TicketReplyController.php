<?php

namespace App\Http\Controllers;

use App\Actions\CreateTicketReplyAction;
use App\Http\Requests\StoreTicketReplyRequest;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\RedirectResponse;

class TicketReplyController extends Controller
{
    public function store(StoreTicketReplyRequest $request, Ticket $ticket, CreateTicketReplyAction $action): RedirectResponse
    {
        $this->authorize('create', [TicketReply::class, $ticket]);

        $action->execute($ticket, $request->user(), $request->validated());

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Reply added.');
    }

    public function destroy(Ticket $ticket, TicketReply $reply): RedirectResponse
    {
        $this->authorize('delete', $reply);

        $reply->delete();

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Reply deleted.');
    }
}
