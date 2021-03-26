<?php

declare(strict_types=1);

namespace App\Shipments\Domain;

use DateInterval;

interface Cacheable
{
    public static function generateCacheKey(string $identifier): string;

    public function getCacheKey(): string;

    public static function getCacheTtl(): DateInterval;
}
