<?php

namespace App\Http\Controllers\Staff;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketBoardController extends Controller
{
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status');
        $assigneeFilter = $request->query('assignee');
        $scope = $request->query('scope', 'all'); // all | mine | unassigned

        $tickets = Ticket::query()
            ->with(['requester', 'assignee'])
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->when($assigneeFilter, fn ($q) => $q->where('assignee_id', $assigneeFilter))
            ->when($scope === 'mine', fn ($q) => $q->where('assignee_id', $request->user()->id))
            ->when($scope === 'unassigned', fn ($q) => $q->whereNull('assignee_id'))
            ->when(! $statusFilter, fn ($q) => $q->active())
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $staffUsers = User::where('role', '!=', 'user')->orderBy('name')->get(['id', 'name', 'display_name']);

        return Inertia::render('staff/tickets/Index', [
            'tickets' => $tickets,
            'staffUsers' => $staffUsers,
            'statuses' => collect(TicketStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'filters' => [
                'status' => $statusFilter,
                'assignee' => $assigneeFilter,
                'scope' => $scope,
            ],
        ]);
    }
}
