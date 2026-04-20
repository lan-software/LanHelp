<?php

use App\Actions\SyncUserRolesFromLanCore;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'lancore.enabled' => true,
        'lancore.internal_url' => null,
        'lancore.base_url' => 'http://lancore.test',
        'lancore.token' => 'lci_test_token',
        'lancore.retries' => 0,
    ]);
});

// ── Direct roles (no API call) ────────────────────────────────────────────────

it('sets role to admin when provided roles include admin', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    Http::fake();

    app(SyncUserRolesFromLanCore::class)->handle($user, ['user', 'admin']);

    expect($user->fresh()->role)->toBe(UserRole::Admin);
    Http::assertNothingSent();
});

it('sets role to user when provided roles include only user', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    Http::fake();

    app(SyncUserRolesFromLanCore::class)->handle($user, ['user']);

    expect($user->fresh()->role)->toBe(UserRole::User);
    Http::assertNothingSent();
});

it('does not change role when provided roles are empty and current role is user', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    Http::fake();

    app(SyncUserRolesFromLanCore::class)->handle($user, []);

    expect($user->fresh()->role)->toBe(UserRole::User);
});

it('maps moderator to the local staff role', function () {
    $user = User::factory()->lanCoreUser(42)->create(['role' => UserRole::User]);
    Http::fake();

    app(SyncUserRolesFromLanCore::class)->handle($user, ['moderator']);

    expect($user->fresh()->role)->toBe(UserRole::Staff);
});

it('maps superadmin to the local admin role', function () {
    $user = User::factory()->lanCoreUser(42)->create(['role' => UserRole::User]);
    Http::fake();

    app(SyncUserRolesFromLanCore::class)->handle($user, ['superadmin']);

    expect($user->fresh()->role)->toBe(UserRole::Admin);
});

// ── Local-only role preservation (Staff) ─────────────────────────────────────

it('preserves staff role when LanCore returns only user role', function () {
    $user = User::factory()->lanCoreUser(42)->staff()->create();
    Http::fake();

    app(SyncUserRolesFromLanCore::class)->handle($user, ['user']);

    expect($user->fresh()->role)->toBe(UserRole::Staff);
});

it('upgrades staff to admin when LanCore grants admin role', function () {
    $user = User::factory()->lanCoreUser(42)->staff()->create();
    Http::fake();

    app(SyncUserRolesFromLanCore::class)->handle($user, ['user', 'admin']);

    expect($user->fresh()->role)->toBe(UserRole::Admin);
});

it('preserves staff role when LanCore returns no recognized roles', function () {
    $user = User::factory()->lanCoreUser(42)->staff()->create();
    Http::fake();

    app(SyncUserRolesFromLanCore::class)->handle($user, []);

    expect($user->fresh()->role)->toBe(UserRole::Staff);
});

// ── SSO callback integration ──────────────────────────────────────────────────

it('syncs roles from SSO exchange response without a second API request', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => Http::response([
            'data' => [
                'id' => 42,
                'username' => 'alice',
                'email' => 'alice@example.com',
                'locale' => 'en',
                'avatar_url' => null,
                'roles' => ['user', 'admin'],
            ],
        ]),
    ]);

    $code = str_repeat('a', 64);

    $this->get(route('lancore.callback', ['code' => $code]))
        ->assertRedirect(route('home'));

    $user = User::where('lancore_user_id', 42)->first();
    expect($user->role)->toBe(UserRole::Admin);

    Http::assertSentCount(1);
});
