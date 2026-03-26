<?php

namespace App\Http\Controllers;

use App\Actions\UpdateTicketStatusAction;
use App\Enums\TicketStatus;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;

class TicketStatusController extends Controller
{
    public function update(UpdateTicketStatusRequest $request, Ticket $ticket, UpdateTicketStatusAction $action): RedirectResponse
    {
        $this->authorize('updateStatus', $ticket);

        $status = TicketStatus::from($request->validated('status'));

        $action->execute($ticket, $status, $request->user());

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Status updated to "'.$status->label().'".');
    }
}
