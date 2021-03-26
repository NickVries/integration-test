<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Item;

use App\Shipments\Domain\Cacheable;
use DateInterval;
use Ramsey\Uuid\UuidInterface;

class Item implements Cacheable
{
    public function __construct(
        private UuidInterface $id,
        private ?string $description,
        private Weight $weight,
        private ?string $pictureUrl
    ) {
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getWeight(): Weight
    {
        return $this->weight;
    }

    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }

    public static function generateCacheKey(string $identifier): string
    {
        return "inventory_item_${identifier}";
    }

    public function getCacheKey(): string
    {
        return self::generateCacheKey($this->id->toString());
    }

    public static function getCacheTtl(): DateInterval
    {
        return new DateInterval('P1M');
    }
}
