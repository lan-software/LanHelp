<?php

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('lets staff assign a ticket to another staff member', function () {
    $staff = User::factory()->staff()->create();
    $assignee = User::factory()->staff()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($staff)
        ->patch(route('staff.tickets.assign', $ticket), ['assignee_id' => $assignee->id])
        ->assertRedirect();

    expect($ticket->fresh()->assignee_id)->toBe($assignee->id);
});

it('prevents regular users from assigning tickets', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->staff()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($user)
        ->patch(route('staff.tickets.assign', $ticket), ['assignee_id' => $assignee->id])
        ->assertForbidden();
});

it('sends an assignment notification to the assignee', function () {
    Notification::fake();

    $staff = User::factory()->staff()->create();
    $assignee = User::factory()->staff()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($staff)
        ->patch(route('staff.tickets.assign', $ticket), ['assignee_id' => $assignee->id]);

    Notification::assertSentTo($assignee, TicketAssignedNotification::class);
});

it('lets staff unassign a ticket', function () {
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->assigned($staff)->create();

    $this->actingAs($staff)
        ->delete(route('staff.tickets.unassign', $ticket))
        ->assertRedirect();

    expect($ticket->fresh()->assignee_id)->toBeNull();
});

it('requires a valid assignee_id', function () {
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($staff)
        ->patch(route('staff.tickets.assign', $ticket), ['assignee_id' => 9999])
        ->assertSessionHasErrors('assignee_id');
});
