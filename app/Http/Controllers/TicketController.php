<?php

namespace App\Http\Controllers;

use App\Actions\CreateTicketAction;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $tickets = Ticket::query()
            ->forRequester($request->user())
            ->with('assignee')
            ->latest()
            ->paginate(20);

        return Inertia::render('tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('tickets/Create', [
            'priorities' => collect(TicketPriority::cases())->map(fn ($p) => ['value' => $p->value, 'label' => $p->label()]),
        ]);
    }

    public function store(StoreTicketRequest $request, CreateTicketAction $action): RedirectResponse
    {
        $ticket = $action->execute($request->user(), $request->validated());

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Your ticket has been submitted.');
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load(['requester', 'assignee']);

        $replies = $ticket->replies()
            ->with('author')
            ->when(! $request->user()->isStaff(), fn ($q) => $q->where('is_internal', false))
            ->get();

        return Inertia::render('tickets/Show', [
            'ticket' => $ticket,
            'replies' => $replies,
            'statuses' => collect(TicketStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'canReply' => $request->user()->can('create', [TicketReply::class, $ticket]),
            'canUpdateStatus' => $request->user()->can('updateStatus', $ticket),
            'canAssign' => $request->user()->can('assign', $ticket),
        ]);
    }
}
