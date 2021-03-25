<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use App\Shipments\Domain\Item\Item;
use JetBrains\PhpStorm\Pure;
use MyParcelCom\Integration\Shipment\Items\Item as ShipmentItem;
use MyParcelCom\Integration\Shipment\Price;
use function bcmul;

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

    #[Pure]
    public function toShipmentItem(?string $orderCurrency): ShipmentItem
    {
        return new ShipmentItem(
            description: (string) $this->description,
            quantity: $this->quantity ? (int) $this->quantity : null,
            imageUrl: $this->item->getPictureUrl(),
            itemValue: new Price((int) (bcmul('100', (string) $this->amountFC)), (string) $orderCurrency),
            itemWeight: $this->item->getWeight()->toGrams(),
        );
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
