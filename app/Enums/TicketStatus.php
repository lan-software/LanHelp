<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingForUser = 'waiting_for_user';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::WaitingForUser => 'Waiting for User',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'blue',
            self::InProgress => 'yellow',
            self::WaitingForUser => 'orange',
            self::Resolved => 'green',
            self::Closed => 'gray',
        };
    }

    /** Returns the statuses that count as "active" (visible on the staff board by default). */
    public static function active(): array
    {
        return [self::Open, self::InProgress, self::WaitingForUser];
    }

    /** Returns the statuses a requester can transition to from the ticket detail page. */
    public static function requesterTransitions(): array
    {
        return [self::Open, self::Closed];
    }

    /** Returns all statuses staff can transition to. */
    public static function staffTransitions(): array
    {
        return self::cases();
    }
}
