<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('prefills the create-ticket form from query params', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tickets.create', [
            'subject' => 'Help with my cart',
            'category' => 'shop',
            'context' => [
                'source_product' => 'lancore',
                'source_domain' => 'http://lancore.test',
                'order_reference' => 'ORD-42',
                'links' => ['http://lancore.test/cart'],
            ],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tickets/Create')
            ->where('prefill.subject', 'Help with my cart')
            ->where('prefill.category', 'shop')
            ->where('prefill.context_snapshot.source_product', 'lancore')
            ->where('prefill.context_snapshot.source_domain', 'http://lancore.test')
            ->where('prefill.context_snapshot.order_reference', 'ORD-42')
            ->where('prefill.context_snapshot.links.0', 'http://lancore.test/cart')
        );
});

it('drops unknown context keys from the prefill', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tickets.create', [
            'context' => [
                'source_product' => 'lancore',
                'attacker' => 'boom',
                'links' => ['http://lancore.test/cart'],
            ],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tickets/Create')
            ->where('prefill.context_snapshot.source_product', 'lancore')
            ->missing('prefill.context_snapshot.attacker')
        );
});

it('renders an empty prefill when no query params are supplied', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tickets.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tickets/Create')
            ->where('prefill.subject', '')
            ->where('prefill.category', '')
            ->where('prefill.context_snapshot', [])
        );
});

it('still allows POST /tickets to succeed without any prefill data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'subject' => 'No prefill',
            'description' => 'Plain submission.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'requester_id' => $user->id,
        'subject' => 'No prefill',
    ]);
});
