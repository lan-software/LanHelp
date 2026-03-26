<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('lets staff update ticket status', function () {
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->open()->create();

    $this->actingAs($staff)
        ->patch(route('tickets.status.update', $ticket), ['status' => 'in_progress'])
        ->assertRedirect();

    expect($ticket->fresh()->status)->toBe(TicketStatus::InProgress);
});

it('lets the requester close their own ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->open()->create(['requester_id' => $user->id]);

    $this->actingAs($user)
        ->patch(route('tickets.status.update', $ticket), ['status' => 'closed'])
        ->assertRedirect();

    expect($ticket->fresh()->status)->toBe(TicketStatus::Closed);
});

it('prevents a different user from changing another user\'s ticket status', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $ticket = Ticket::factory()->open()->create(['requester_id' => $owner->id]);

    $this->actingAs($other)
        ->patch(route('tickets.status.update', $ticket), ['status' => 'closed'])
        ->assertForbidden();
});

it('sets resolved_at when resolving a ticket', function () {
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->open()->create();

    $this->actingAs($staff)
        ->patch(route('tickets.status.update', $ticket), ['status' => 'resolved']);

    expect($ticket->fresh()->resolved_at)->not->toBeNull();
});

it('clears resolved_at when re-opening a resolved ticket', function () {
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->resolved()->create();

    $this->actingAs($staff)
        ->patch(route('tickets.status.update', $ticket), ['status' => 'open']);

    expect($ticket->fresh()->resolved_at)->toBeNull();
});

it('notifies the requester on status change', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->open()->create(['requester_id' => $owner->id]);

    $this->actingAs($staff)
        ->patch(route('tickets.status.update', $ticket), ['status' => 'resolved']);

    Notification::assertSentTo($owner, TicketStatusChangedNotification::class);
});

it('rejects an invalid status value', function () {
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($staff)
        ->patch(route('tickets.status.update', $ticket), ['status' => 'flying'])
        ->assertSessionHasErrors('status');
});
