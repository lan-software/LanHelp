<?php

namespace App\Http\Controllers;

use App\Actions\AssignTicketAction;
use App\Http\Requests\AssignTicketRequest;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class TicketAssignmentController extends Controller
{
    public function update(AssignTicketRequest $request, Ticket $ticket, AssignTicketAction $action): RedirectResponse
    {
        $this->authorize('assign', $ticket);

        $assignee = User::findOrFail($request->validated('assignee_id'));
        $action->assign($ticket, $assignee);

        return redirect()->route('staff.tickets.index')
            ->with('success', "Ticket assigned to {$assignee->displayName()}.");
    }

    public function destroy(Ticket $ticket, AssignTicketAction $action): RedirectResponse
    {
        $this->authorize('assign', $ticket);

        $action->unassign($ticket);

        return redirect()->route('staff.tickets.index')
            ->with('success', 'Ticket unassigned.');
    }
}
