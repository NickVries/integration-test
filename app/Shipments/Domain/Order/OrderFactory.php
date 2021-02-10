<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use App\Shipments\Domain\Address\AddressesGateway;
use App\Shipments\Domain\Address\NullAddress;
use Carbon\Carbon;
use DateTimeInterface;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;
use function array_map;
use function preg_match;

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
            'Created'                   => 'string',
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
            $order['Created'] ? $this->createCreatedAt($order['Created']) : null
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

    private function createCreatedAt(string $createdDate): ?DateTimeInterface
    {
        $createdAt = null;

        if (preg_match('/^\/Date\((?P<timestamp>\d+?)\)\/$/', $createdDate, $matches)) {
            // We have to divide the timestamp by 1000 to get the timestamp in seconds instead of milliseconds.
            $createdAt = Carbon::createFromTimestamp((int) ($matches['timestamp'] / 1000));
        }

        return $createdAt;
    }
}
