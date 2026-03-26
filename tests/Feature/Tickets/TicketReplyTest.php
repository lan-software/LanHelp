<?php

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewReplyNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('lets the requester reply to their own open ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('tickets.replies.store', $ticket), ['body' => 'Here is my reply.'])
        ->assertRedirect(route('tickets.show', $ticket));

    $this->assertDatabaseHas('ticket_replies', [
        'ticket_id' => $ticket->id,
        'author_id' => $user->id,
        'body' => 'Here is my reply.',
        'is_internal' => false,
    ]);
});

it('prevents the requester from replying to a resolved ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->resolved()->create(['requester_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('tickets.replies.store', $ticket), ['body' => 'I still need help.'])
        ->assertForbidden();
});

it('prevents a different user from replying to someone else\'s ticket', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $owner->id]);

    $this->actingAs($other)
        ->post(route('tickets.replies.store', $ticket), ['body' => 'Sneaky reply.'])
        ->assertForbidden();
});

it('lets staff reply to any ticket', function () {
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($staff)
        ->post(route('tickets.replies.store', $ticket), ['body' => 'Staff response.'])
        ->assertRedirect();

    $this->assertDatabaseHas('ticket_replies', [
        'ticket_id' => $ticket->id,
        'body' => 'Staff response.',
    ]);
});

it('lets staff create internal notes', function () {
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($staff)
        ->post(route('tickets.replies.store', $ticket), [
            'body' => 'Internal observation.',
            'is_internal' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('ticket_replies', [
        'ticket_id' => $ticket->id,
        'is_internal' => true,
    ]);
});

it('notifies the requester when staff replies', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $owner->id]);

    $this->actingAs($staff)
        ->post(route('tickets.replies.store', $ticket), ['body' => 'We are looking into it.']);

    Notification::assertSentTo($owner, NewReplyNotification::class);
});

it('does not notify for internal notes', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $staff = User::factory()->staff()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $owner->id]);

    $this->actingAs($staff)
        ->post(route('tickets.replies.store', $ticket), [
            'body' => 'Internal only.',
            'is_internal' => true,
        ]);

    Notification::assertNothingSent();
});

it('requires body to be present', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('tickets.replies.store', $ticket), ['body' => ''])
        ->assertSessionHasErrors('body');
});
