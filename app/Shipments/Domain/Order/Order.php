<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use App\Shipments\Domain\Address\Address;
use DateTimeInterface;
use JetBrains\PhpStorm\Immutable;
use MyParcelCom\Integration\Shipment\Items\ItemCollection;
use MyParcelCom\Integration\Shipment\PhysicalProperties;
use MyParcelCom\Integration\Shipment\Price;
use MyParcelCom\Integration\Shipment\Shipment;
use MyParcelCom\Integration\ShopId;
use function array_filter;
use function bcmul;

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
        private ?DateTimeInterface $createdAt
    ) {
    }

    public function toShipment(ShopId $shopId, string $channel): Shipment
    {
        $price = new Price((int) bcmul((string) $this->amountFC, '100'), (string) $this->currency);

        return new Shipment(
            shopId: $shopId,
            createdAt: $this->createdAt,
            recipientAddress: $this->deliveryAddress->toShipmentAddress(),
            description: $this->description,
            customerReference: (string) $this->orderId,
            channel: $channel,
            totalValue: $price,
            price: $price,
            physicalProperties: new PhysicalProperties($this->orderLines->sumWeight()),
            items: $this->orderLines->toShipmentItems($this->currency),
            tags: array_filter([$this->shippingMethodDescription])
        );
    }
}
