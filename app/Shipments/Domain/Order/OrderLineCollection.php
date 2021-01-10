<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use Illuminate\Support\Collection;
use function array_filter;
use function array_map;

class OrderLineCollection extends Collection
{
    public function toJsonApiArray(?string $orderCurrency): array
    {
        return array_map(
            static fn(OrderLine $orderLine) => array_filter([
                'description' => $orderLine->getDescription(),
                'image_url'   => $orderLine->getItem()->getPictureUrl(),
                'item_value'  => array_filter([
                    'amount'   => $orderLine->getAmountFC(),
                    'currency' => $orderCurrency,
                ]),
                'quantity'    => (int) $orderLine->getQuantity(),
                'item_weight' => $orderLine->getItem()->getWeight()->toGrams(),
            ]),
            $this->items
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
