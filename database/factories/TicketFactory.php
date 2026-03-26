<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),
            'assignee_id' => null,
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraphs(2, true),
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::Normal,
            'category' => fake()->optional(0.6)->randomElement(['general', 'technical', 'billing', 'account']),
            'context_snapshot' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => TicketStatus::Open]);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => TicketStatus::InProgress]);
    }

    public function resolved(): static
    {
        return $this->state([
            'status' => TicketStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state([
            'status' => TicketStatus::Closed,
            'closed_at' => now(),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(['priority' => TicketPriority::Urgent]);
    }

    public function assigned(User $user): static
    {
        return $this->state(['assignee_id' => $user->id]);
    }

    public function withContext(): static
    {
        return $this->state([
            'context_snapshot' => [
                'source_product' => 'LanCore',
                'source_domain' => 'events',
                'event_reference' => 'EVT-'.fake()->numerify('####'),
                'seat_reference' => 'SEAT-'.fake()->numerify('###'),
                'links' => [
                    'event' => 'https://lancore.lan/events/1',
                ],
            ],
        ]);
    }
}
