<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Item;

class Item
{
    public function __construct(
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
}
