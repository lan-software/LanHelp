<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class LanCoreAnnouncementFeed
{
    private const CACHE_KEY = 'lancore.announcements.feed';

    private const CACHE_TTL_SECONDS = 60;

    /**
     * Fetch the LanCore announcements feed. Returns an empty array on any failure.
     *
     * @return list<array<string, mixed>>
     */
    public function fetch(): array
    {
        $url = (string) config('lancore.announcements_feed_url');

        if ($url === '') {
            return [];
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () use ($url): array {
            try {
                $response = Http::timeout(2)->retry(1, 100)->get($url);

                if (! $response->successful()) {
                    return [];
                }

                $json = $response->json();

                return is_array($json) ? array_values($json) : [];
            } catch (Throwable) {
                return [];
            }
        });
    }
}
