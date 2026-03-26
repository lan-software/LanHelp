<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('allows authenticated users to create a ticket', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'subject' => 'My first ticket',
            'description' => 'Something is broken.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'requester_id' => $user->id,
        'subject' => 'My first ticket',
        'status' => TicketStatus::Open->value,
    ]);
});

it('requires subject and description', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [])
        ->assertSessionHasErrors(['subject', 'description']);
});

it('defaults priority to normal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'subject' => 'Test',
            'description' => 'Details',
        ]);

    expect(Ticket::latest()->first()->priority)->toBe(TicketPriority::Normal);
});

it('accepts a valid priority', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'subject' => 'Urgent problem',
            'description' => 'Very urgent.',
            'priority' => 'urgent',
        ]);

    expect(Ticket::latest()->first()->priority)->toBe(TicketPriority::Urgent);
});

it('stores a context snapshot when provided', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'subject' => 'Context ticket',
            'description' => 'Has context.',
            'context_snapshot' => [
                'source_product' => 'LanCore',
                'event_reference' => 'EVT-1234',
            ],
        ]);

    $ticket = Ticket::latest()->first();
    expect($ticket->context_snapshot['event_reference'])->toBe('EVT-1234');
});

it('sends a confirmation notification to the requester', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'subject' => 'Notification test',
            'description' => 'Should receive email.',
        ]);

    Notification::assertSentTo($user, TicketCreatedNotification::class);
});

it('redirects unauthenticated users to login', function () {
    $this->post(route('tickets.store'), [
        'subject' => 'Anon',
        'description' => 'Anon attempt',
    ])->assertRedirect(route('login'));
});
