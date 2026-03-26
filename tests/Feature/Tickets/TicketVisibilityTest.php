<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets a requester view their own ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('tickets.show', $ticket))
        ->assertSuccessful();
});

it('prevents a user from viewing another user\'s ticket', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('tickets.show', $ticket))
        ->assertForbidden();
});

it('lets staff view any ticket', function () {
    $staff = User::factory()->staff()->create();
    $owner = User::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $owner->id]);

    $this->actingAs($staff)
        ->get(route('tickets.show', $ticket))
        ->assertSuccessful();
});

it('shows only own tickets on the my-tickets index', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $own = Ticket::factory()->create(['requester_id' => $user->id, 'subject' => 'My ticket']);
    $foreign = Ticket::factory()->create(['requester_id' => $other->id, 'subject' => 'Their ticket']);

    $response = $this->actingAs($user)
        ->get(route('tickets.index'));

    $response->assertSuccessful();
    $data = $response->original->getData()['page']['props'];
    $ids = collect($data['tickets']['data'])->pluck('id');

    expect($ids)->toContain($own->id)
        ->not->toContain($foreign->id);
});
