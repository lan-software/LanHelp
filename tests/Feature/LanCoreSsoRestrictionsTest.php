<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

// ── Profile update ────────────────────────────────────────────────────────────

it('prevents an SSO user from updating their profile', function () {
    $user = User::factory()->lanCoreUser()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])
        ->assertForbidden();
});

it('allows a local user to update their profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ])
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->name)->toBe('New Name');
});

// ── Account deletion ──────────────────────────────────────────────────────────

it('prevents an SSO user from deleting their account', function () {
    $user = User::factory()->lanCoreUser()->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'anything'])
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

it('allows a local user to delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect('/');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

// ── Password change ───────────────────────────────────────────────────────────

it('prevents an SSO user from changing their password', function () {
    $user = User::factory()->lanCoreUser()->create();

    $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'anything',
            'password' => 'new-password-123!',
            'password_confirmation' => 'new-password-123!',
        ])
        ->assertForbidden();
});

it('allows a local user to change their password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password-123!',
            'password_confirmation' => 'new-password-123!',
        ])
        ->assertRedirect();
});

// ── Password reset email ──────────────────────────────────────────────────────

it('does not send a password reset notification to an SSO user', function () {
    Notification::fake();

    $user = User::factory()->lanCoreUser()->create(['email' => 'sso@example.com']);

    $this->post(route('password.request'), ['email' => 'sso@example.com'])
        ->assertRedirect();

    Notification::assertNothingSent();
});

it('sends a password reset notification to a local user', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'local@example.com']);

    $this->post(route('password.request'), ['email' => 'local@example.com'])
        ->assertRedirect();

    Notification::assertSentTo($user, ResetPassword::class);
});

// ── Security page access ──────────────────────────────────────────────────────

it('allows an SSO user to access the security settings page without password confirmation', function () {
    $user = User::factory()->lanCoreUser()->create();

    $this->actingAs($user)
        ->get(route('security.edit'))
        ->assertOk();
});
