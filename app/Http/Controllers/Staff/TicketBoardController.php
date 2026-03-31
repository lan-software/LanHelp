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
        $search = $request->query('search');
        $sort = $request->query('sort', 'updated_at');
        $direction = $request->query('direction', 'desc');

        $allowedSorts = ['id', 'subject', 'status', 'priority', 'updated_at', 'created_at'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'updated_at';
        }
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $tickets = Ticket::query()
            ->with(['requester', 'assignee'])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhereHas('requester', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('display_name', 'like', "%{$search}%"));
            }))
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->when($assigneeFilter, fn ($q) => $q->where('assignee_id', $assigneeFilter))
            ->when($scope === 'mine', fn ($q) => $q->where('assignee_id', $request->user()->id))
            ->when($scope === 'unassigned', fn ($q) => $q->whereNull('assignee_id'))
            ->when(! $statusFilter, fn ($q) => $q->active())
            ->orderBy($sort, $direction)
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
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }
}
