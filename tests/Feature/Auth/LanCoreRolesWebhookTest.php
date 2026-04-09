<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function lanHelpRolesWebhookHeaders(string $body, string $secret): array
{
    return [
        'X-Webhook-Event' => 'user.roles_updated',
        'X-Webhook-Signature' => 'sha256='.hash_hmac('sha256', $body, $secret),
        'Content-Type' => 'application/json',
    ];
}

beforeEach(function () {
    config(['lancore.webhooks.secret' => 'lanhelp-webhook-secret']);
});

it('syncs LanHelp roles from the LanCore webhook payload', function () {
    $user = User::factory()->lanCoreUser(42)->create(['role' => UserRole::User]);

    $body = json_encode([
        'event' => 'user.roles_updated',
        'user' => [
            'id' => 42,
            'username' => $user->name,
            'roles' => ['moderator'],
        ],
        'changes' => [
            'added' => ['moderator'],
            'removed' => ['user'],
        ],
    ], JSON_THROW_ON_ERROR);

    $this->postJson('/api/webhooks/roles', json_decode($body, true), lanHelpRolesWebhookHeaders($body, 'lanhelp-webhook-secret'))
        ->assertOk()
        ->assertJson(['status' => 'ok']);

    expect($user->fresh()->role)->toBe(UserRole::Staff);
});

it('rejects a roles webhook with an invalid signature', function () {
    $user = User::factory()->lanCoreUser(42)->create();

    $body = json_encode([
        'event' => 'user.roles_updated',
        'user' => [
            'id' => $user->lancore_user_id,
            'username' => $user->name,
            'roles' => ['admin'],
        ],
        'changes' => [
            'added' => ['admin'],
            'removed' => [],
        ],
    ], JSON_THROW_ON_ERROR);

    $this->postJson('/api/webhooks/roles', json_decode($body, true), [
        'X-Webhook-Event' => 'user.roles_updated',
        'X-Webhook-Signature' => 'sha256=invalid',
        'Content-Type' => 'application/json',
    ])->assertUnauthorized();
});