<?php

use App\Models\KnowledgeBaseArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Public listing ────────────────────────────────────────────────────────────

it('shows published articles to guests', function () {
    $published = KnowledgeBaseArticle::factory()->published()->create(['title' => 'Public Article']);
    $draft = KnowledgeBaseArticle::factory()->draft()->create(['title' => 'Draft Article']);

    $response = $this->get(route('kb.index'))->assertSuccessful();
    $ids = collect($response->original->getData()['page']['props']['articles'])->pluck('id');

    expect($ids)->toContain($published->id)
        ->not->toContain($draft->id);
});

it('allows viewing a published article as a guest', function () {
    $article = KnowledgeBaseArticle::factory()->published()->create();

    $this->get(route('kb.show', $article))->assertSuccessful();
});

it('prevents guests from viewing draft articles', function () {
    $draft = KnowledgeBaseArticle::factory()->draft()->create();

    $this->get(route('kb.show', $draft))->assertForbidden();
});

it('allows staff to view draft articles', function () {
    $staff = User::factory()->staff()->create();
    $draft = KnowledgeBaseArticle::factory()->draft()->create();

    $this->actingAs($staff)
        ->get(route('kb.show', $draft))
        ->assertSuccessful();
});

// ── Admin management ──────────────────────────────────────────────────────────

it('lets admins create articles', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.kb.store'), [
            'title' => 'New KB Article',
            'content' => 'Detailed explanation here.',
            'is_published' => false,
        ])
        ->assertRedirect(route('admin.kb.index'));

    $this->assertDatabaseHas('knowledge_base_articles', [
        'title' => 'New KB Article',
        'is_published' => false,
    ]);
});

it('publishes an article when is_published is true', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.kb.store'), [
            'title' => 'Published Article',
            'content' => 'Content.',
            'is_published' => true,
        ]);

    $article = KnowledgeBaseArticle::where('title', 'Published Article')->first();
    expect($article->is_published)->toBeTrue();
    expect($article->published_at)->not->toBeNull();
});

it('prevents non-admins from creating articles', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.kb.store'), [
            'title' => 'Sneaky Article',
            'content' => 'Content.',
        ])
        ->assertForbidden();
});

it('lets admins update articles', function () {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create(['author_id' => $admin->id]);

    $this->actingAs($admin)
        ->patch(route('admin.kb.update', $article), [
            'title' => 'Updated Title',
            'content' => 'Updated content.',
        ])
        ->assertRedirect(route('admin.kb.index'));

    expect($article->fresh()->title)->toBe('Updated Title');
});

it('lets admins delete articles', function () {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create(['author_id' => $admin->id]);

    $this->actingAs($admin)
        ->delete(route('admin.kb.destroy', $article))
        ->assertRedirect(route('admin.kb.index'));

    $this->assertDatabaseMissing('knowledge_base_articles', ['id' => $article->id]);
});

it('prevents non-admins from accessing the admin KB index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.kb.index'))
        ->assertForbidden();
});
