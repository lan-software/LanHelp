<?php

use App\Services\LanCoreAnnouncementFeed;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    Cache::forget('lancore.announcements.feed');
    Config::set('lancore.announcements_feed_url', 'http://lancore.test/api/announcements/feed');
});

it('returns decoded array on 200 response', function (): void {
    Http::fake([
        'http://lancore.test/*' => Http::response([
            ['id' => 1, 'title' => 'Hello', 'audience' => 'satellites', 'severity' => 'info', 'body' => null, 'starts_at' => null, 'ends_at' => null, 'dismissible' => true],
        ], 200),
    ]);

    $result = (new LanCoreAnnouncementFeed)->fetch();

    expect($result)->toBeArray()->toHaveCount(1);
    expect($result[0]['title'])->toBe('Hello');
});

it('returns empty array on 500 response', function (): void {
    Http::fake([
        'http://lancore.test/*' => Http::response('boom', 500),
    ]);

    expect((new LanCoreAnnouncementFeed)->fetch())->toBe([]);
});

it('returns empty array on transport exception', function (): void {
    Http::fake(function (): void {
        throw new ConnectionException('timeout');
    });

    expect((new LanCoreAnnouncementFeed)->fetch())->toBe([]);
});

it('caches responses across calls within TTL', function (): void {
    Http::fake([
        'http://lancore.test/*' => Http::response([], 200),
    ]);

    $service = new LanCoreAnnouncementFeed;
    $service->fetch();
    $service->fetch();
    $service->fetch();

    Http::assertSentCount(1);
});
