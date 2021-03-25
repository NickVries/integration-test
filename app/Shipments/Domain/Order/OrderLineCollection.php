<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use Illuminate\Support\Collection;
use MyParcelCom\Integration\Shipment\Items\ItemCollection;
use function array_map;

class OrderLineCollection extends Collection
{
    public function toShipmentItems(?string $orderCurrency): ItemCollection
    {
        return new ItemCollection(
            ...array_map(
                static fn(OrderLine $orderLine) => $orderLine->toShipmentItem($orderCurrency),
                $this->items
            )
        );
    }

    public function sumWeight(): int
    {
        return $this->reduce(
            fn(int $accumulator, OrderLine $orderLine) => $accumulator + $orderLine->getItem()->getWeight()->toGrams(),
            0
        );
    }
}
