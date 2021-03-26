<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use App\Shipments\Domain\Item\ItemsGateway;
use App\Shipments\Domain\Item\NullItem;
use GuzzleHttp\Client;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;

class OrderLineFactory
{
    public function __construct(
        private ItemsGateway $itemsGateway
    ) {
    }

    public function createFromArray(
        #[ArrayShape([
            'AmountFC'        => 'float',
            'Description'     => 'string',
            'ItemDescription' => 'string',
            'Quantity'        => 'float',
            'Item'            => 'string',
        ])]
        array $orderLine,
        Client $client
    ): OrderLine {
        return new OrderLine(
            $orderLine['AmountFC'],
            $orderLine['Description'],
            $orderLine['ItemDescription'],
            $orderLine['Quantity'],
            $orderLine['Item'] ?
                $this->itemsGateway->fetchOneByItemId(Uuid::fromString($orderLine['Item']), $client) :
                new NullItem()
        );
    }
}
