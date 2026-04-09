<?php

use LanSoftware\LanCoreClient\DTOs\LanCoreUser;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'lancore.enabled' => true,
        'lancore.internal_url' => null,
        'lancore.base_url' => 'http://lancore.test',
        'lancore.token' => 'lci_test_token',
        'lancore.app_slug' => 'lanhelp',
    ]);
});

// ── Redirect ─────────────────────────────────────────────────────────────────

it('redirects to LanCore SSO when enabled', function () {
    $this->get(route('lancore.redirect'))
        ->assertRedirectContains('lancore.test/sso/authorize');
});

it('redirects to local login when LanCore is disabled', function () {
    config(['lancore.enabled' => false]);

    $this->get(route('lancore.redirect'))
        ->assertRedirect(route('login'));
});

// ── Callback ─────────────────────────────────────────────────────────────────

it('creates a new shadow user on first successful SSO login', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => Http::response([
            'data' => [
                'id' => 42,
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'locale' => 'en',
                'avatar_url' => null,
                'roles' => ['user'],
            ],
        ]),
    ]);

    $code = str_repeat('a', 64);

    $this->get(route('lancore.callback', ['code' => $code]))
        ->assertRedirect(route('home'));

    $this->assertDatabaseHas('users', [
        'lancore_user_id' => 42,
        'name' => 'johndoe',
        'email' => 'john@example.com',
    ]);
});

it('updates an existing shadow user on repeat SSO login', function () {
    $existing = User::factory()->lanCoreUser(42)->create(['name' => 'old_name']);

    Http::fake([
        '*/api/integration/sso/exchange' => Http::response([
            'data' => [
                'id' => 42,
                'username' => 'johndoe_updated',
                'email' => 'john@example.com',
                'locale' => 'en',
                'avatar_url' => null,
                'roles' => [],
            ],
        ]),
    ]);

    $code = str_repeat('b', 64);

    $this->get(route('lancore.callback', ['code' => $code]))
        ->assertRedirect(route('home'));

    expect($existing->fresh()->name)->toBe('johndoe_updated');
});

it('rejects a callback code shorter than 64 characters', function () {
    $this->get(route('lancore.callback', ['code' => 'tooshort']))
        ->assertRedirect(route('home'));
});

it('redirects with error when LanCore returns an expired code', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => Http::response(
            ['error' => 'The authorization code has expired or was already used.'],
            400
        ),
    ]);

    $code = str_repeat('c', 64);

    $this->get(route('lancore.callback', ['code' => $code]))
        ->assertRedirect(route('home'));
});

it('redirects with error when LanCore is unreachable', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => function () {
            throw new ConnectionException('Connection refused');
        },
    ]);

    config(['lancore.http.retries' => 0]);

    $code = str_repeat('d', 64);

    $this->get(route('lancore.callback', ['code' => $code]))
        ->assertRedirect(route('home'));
});

// ── Status endpoint ───────────────────────────────────────────────────────────

it('returns enabled=true when LanCore is configured', function () {
    $this->getJson(route('lancore.status'))
        ->assertSuccessful()
        ->assertJson(['enabled' => true]);
});

it('returns enabled=false when LanCore is disabled', function () {
    config(['lancore.enabled' => false]);

    $this->getJson(route('lancore.status'))
        ->assertSuccessful()
        ->assertJson(['enabled' => false]);
});

// ── UserSyncService unit-level tests ─────────────────────────────────────────

it('preserves existing display_name on re-sync', function () {
    $user = User::factory()->lanCoreUser(99)->create(['display_name' => 'Custom Name']);
    $dto = new LanCoreUser(id: 99, username: 'newusername', email: 'new@example.com', locale: 'en');

    $service = app(UserSyncService::class);
    $service->resolveFromUpstream($dto);

    expect($user->fresh()->display_name)->toBe('Custom Name');
});
