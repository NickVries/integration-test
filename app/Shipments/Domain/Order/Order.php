<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use App\Authentication\Domain\ShopId;
use App\Shipments\Domain\Address\Address;
use JetBrains\PhpStorm\Immutable;
use function array_filter;

#[Immutable]
class Order
{
    public function __construct(
        private ?string $orderId,
        private string $description,
        private ?string $shippingMethodDescription,
        private float $amountFC,
        private ?string $currency,
        private Address $deliveryAddress,
        private OrderLineCollection $orderLines,
    ) {
    }

    public function toJsonApiArray(ShopId $shopId, string $channel): array
    {
        return [
            'type'          => 'shipments',
            'attributes'    => array_filter([
                'recipient_address'   => $this->deliveryAddress->toJsonApiArray(),
                'description'         => $this->description,
                'customer_reference'  => $this->orderId,
                'channel'             => $channel,
                'total_value'         => array_filter([
                    'amount'   => (int) $this->amountFC,
                    'currency' => $this->currency,
                ]),
                'price'               => array_filter([
                    'amount'   => (int) $this->amountFC,
                    'currency' => $this->currency,
                ]),
                'physical_properties' => array_filter([
                    'weight' => $this->orderLines->sumWeight(),
                ]),
                'items'               => array_filter($this->orderLines->toJsonApiArray($this->currency)),
                'tags'                => array_filter([
                    $this->shippingMethodDescription,
                ]),
            ]),
            'relationships' => [
                'shop' => [
                    'data' => [
                        'type' => 'shops',
                        'id'   => $shopId->toString(),
                    ],
                ],
            ],
        ];
    }
}
