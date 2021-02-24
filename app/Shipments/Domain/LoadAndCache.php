<?php

declare(strict_types=1);

namespace App\Shipments\Domain;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

trait LoadAndCache
{
    /** @noinspection PhpUnhandledExceptionInspection */
    private function loadCached(string $cacheKey, callable $resolver, DateInterval $ttl, CacheInterface $cache)
    {
        if ($cache->has($cacheKey)) {
            $payload = $cache->get($cacheKey);
        } else {
            $payload = $resolver();
            $cache->set($cacheKey, $payload, $ttl);
        }
        return $payload;
    }
}
