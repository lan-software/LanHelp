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
use Illuminate\Support\Str;
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

    public function create(Request $request): Response
    {
        return Inertia::render('tickets/Create', [
            'priorities' => collect(TicketPriority::cases())->map(fn ($p) => ['value' => $p->value, 'label' => $p->label()]),
            'prefill' => [
                'subject' => Str::limit((string) $request->string('subject'), 255, ''),
                'category' => Str::limit((string) $request->string('category'), 100, ''),
                'context_snapshot' => $this->sanitizeContextSnapshot($request->input('context', [])),
            ],
        ]);
    }

    public function store(StoreTicketRequest $request, CreateTicketAction $action): RedirectResponse
    {
        $ticket = $action->execute($request->user(), $request->validated());

        return redirect()->route('tickets.show', $ticket)
            ->with('success', __('messages.tickets.created'));
    }

    /**
     * Whitelist deep-link-provided context fields so a crafted URL can only
     * populate the same keys that StoreTicketRequest already validates.
     *
     * @param  mixed  $context
     * @return array<string, mixed>
     */
    private function sanitizeContextSnapshot($context): array
    {
        if (! is_array($context)) {
            return [];
        }

        $scalarKeys = [
            'source_product',
            'source_domain',
            'event_reference',
            'seat_reference',
            'tournament_reference',
            'order_reference',
            'team_reference',
        ];

        $snapshot = [];

        foreach ($scalarKeys as $key) {
            if (isset($context[$key]) && is_scalar($context[$key])) {
                $snapshot[$key] = Str::limit((string) $context[$key], 100, '');
            }
        }

        if (isset($context['links']) && is_array($context['links'])) {
            $snapshot['links'] = collect($context['links'])
                ->filter(fn ($link) => is_string($link))
                ->map(fn ($link) => Str::limit($link, 2048, ''))
                ->take(10)
                ->values()
                ->all();
        }

        return $snapshot;
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
