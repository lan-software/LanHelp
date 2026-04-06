<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\KnowledgeBaseArticle;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $this->stats(),
            'recentTickets' => $this->recentTickets(),
            'ticketsByCategory' => $this->ticketsByCategory(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stats(): array
    {
        $totalTickets = Ticket::count();
        $openTickets = Ticket::where('status', TicketStatus::Open)->count();
        $inProgressTickets = Ticket::where('status', TicketStatus::InProgress)->count();
        $waitingTickets = Ticket::where('status', TicketStatus::WaitingForUser)->count();
        $resolvedTickets = Ticket::where('status', TicketStatus::Resolved)->count();
        $closedTickets = Ticket::where('status', TicketStatus::Closed)->count();

        $urgentTickets = Ticket::whereIn('status', [
            TicketStatus::Open,
            TicketStatus::InProgress,
            TicketStatus::WaitingForUser,
        ])->where('priority', TicketPriority::Urgent)->count();

        $unassignedTickets = Ticket::whereIn('status', [
            TicketStatus::Open,
            TicketStatus::InProgress,
        ])->whereNull('assignee_id')->count();

        $publishedArticles = KnowledgeBaseArticle::where('is_published', true)->count();
        $draftArticles = KnowledgeBaseArticle::where('is_published', false)->count();

        $totalUsers = User::count();
        $staffCount = User::whereIn('role', ['staff', 'admin'])->count();

        return [
            'totalTickets' => $totalTickets,
            'openTickets' => $openTickets,
            'inProgressTickets' => $inProgressTickets,
            'waitingTickets' => $waitingTickets,
            'resolvedTickets' => $resolvedTickets,
            'closedTickets' => $closedTickets,
            'urgentTickets' => $urgentTickets,
            'unassignedTickets' => $unassignedTickets,
            'publishedArticles' => $publishedArticles,
            'draftArticles' => $draftArticles,
            'totalUsers' => $totalUsers,
            'staffCount' => $staffCount,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentTickets(): array
    {
        return Ticket::with(['requester:id,name', 'assignee:id,name'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Ticket $ticket) => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'status' => $ticket->status->value,
                'statusLabel' => $ticket->status->label(),
                'statusColor' => $ticket->status->color(),
                'priority' => $ticket->priority->value,
                'priorityLabel' => $ticket->priority->label(),
                'priorityColor' => $ticket->priority->color(),
                'requester' => $ticket->requester?->name,
                'assignee' => $ticket->assignee?->name,
                'createdAt' => $ticket->created_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * @return array<int, array{category: string, count: int}>
     */
    private function ticketsByCategory(): array
    {
        return Ticket::query()
            ->select('category', DB::raw('count(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'category' => ucfirst((string) $row->category),
                'count' => (int) $row->count,
            ])
            ->all();
    }
}
