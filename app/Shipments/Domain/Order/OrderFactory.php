<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use App\Shipments\Domain\Address\AddressesGateway;
use App\Shipments\Domain\Address\NullAddress;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;
use function array_map;

class OrderFactory
{
    public function __construct(
        private AddressesGateway $addressesGateway,
        private OrderLineFactory $orderLineFactory
    ) {
    }

    public function createFromArray(
        #[ArrayShape([
            'OrderID'                   => 'string',
            'Description'               => 'string',
            'ShippingMethodDescription' => 'string',
            'AmountFC'                  => 'float',
            'Currency'                  => 'string',
            'DeliveryAddress'           => 'string',
            'SalesOrderLines'           => 'array',
        ])]
        array $order
    ): Order {
        return new Order(
            $order['OrderID'],
            $order['Description'] ?? '',
            $order['ShippingMethodDescription'] ?? null,
            (float) $order['AmountFC'],
            $order['Currency'],
            $order['DeliveryAddress'] ? $this->addressesGateway->fetchOneByAddressId(Uuid::fromString($order['DeliveryAddress'])) : new NullAddress(),
            $this->createOrderLineCollection($order['SalesOrderLines']['results'] ?? []),
        );
    }

    private function createOrderLineCollection(array $results): OrderLineCollection
    {
        return new OrderLineCollection(
            array_map(
                fn(array $line) => $this->orderLineFactory->createFromArray($line),
                $results
            )
        );
    }
}
