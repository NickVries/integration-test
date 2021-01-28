<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use App\Shipments\Domain\Item\Item;

class OrderLine
{
    public function __construct(
        private ?float $amountFC,
        private ?string $description,
        private ?string $itemDescription,
        private ?float $quantity,
        private Item $item,
    ) {
    }

    public function getAmountFC(): ?float
    {
        return $this->amountFC;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getItemDescription(): ?string
    {
        return $this->itemDescription;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
