<?php

namespace Illimi\Communication\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

class PresenceService
{
    private const TTL_SECONDS = 90;

    public function touch(string $userId, ?string $organizationId = null): array
    {
        $organizationId = $organizationId ?: 'global';
        $timestamp = now()->toIso8601String();

        Cache::put($this->key($organizationId, $userId), $timestamp, now()->addSeconds(self::TTL_SECONDS));

        return [
            'is_online' => true,
            'last_seen_at' => $timestamp,
        ];
    }

    public function status(string $userId, ?string $organizationId = null): array
    {
        $organizationId = $organizationId ?: 'global';
        $timestamp = Cache::get($this->key($organizationId, $userId));

        if (!is_string($timestamp) || trim($timestamp) === '') {
            return [
                'is_online' => false,
                'last_seen_at' => null,
            ];
        }

        try {
            $lastSeenAt = CarbonImmutable::parse($timestamp);
        } catch (\Throwable) {
            return [
                'is_online' => false,
                'last_seen_at' => null,
            ];
        }

        return [
            'is_online' => $lastSeenAt->greaterThan(now()->subSeconds(self::TTL_SECONDS)),
            'last_seen_at' => $lastSeenAt->toIso8601String(),
        ];
    }

    private function key(string $organizationId, string $userId): string
    {
        return sprintf('illimi-communication:presence:%s:%s', $organizationId, $userId);
    }
}
