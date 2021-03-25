<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Order;

use App\Shipments\Domain\Address\Address;
use App\Shipments\Domain\Address\AddressesGateway;
use App\Shipments\Domain\Item\Item;
use App\Shipments\Domain\Item\Weight;
use App\Shipments\Domain\Order\OrderFactory;
use App\Shipments\Domain\Order\OrderLine;
use App\Shipments\Domain\Order\OrderLineFactory;
use Faker\Factory;
use Mockery;
use Mockery\MockInterface;
use MyParcelCom\Integration\Shipment\Address as ShipmentAddress;
use MyParcelCom\Integration\Shipment\Items\Item as ShipmentItem;
use MyParcelCom\Integration\ShopId;
use PHPUnit\Framework\TestCase;
use function bcmul;
use function random_int;

class OrderFactoryTest extends TestCase
{
    public function test_should_create_order_from_array(): void
    {
        $faker = Factory::create();

        $orderDescription = $faker->text;
        $orderId = $faker->uuid;
        $createdAt = $faker->unixTime;
        $orderAmount = random_int(1000, 9000);
        $orderCurrencyCode = $faker->currencyCode;
        $orderShippingMethodDescription = $faker->word;

        $orderLineAmount = random_int(1000, 9000);
        $orderLineDescription = $faker->text;
        $orderLineItemDescription = $faker->text;
        $orderLineQuantity = random_int(1, 10);

        $itemWeight = random_int(1, 100);
        $itemPictureUrl = $faker->imageUrl();

        $addressMock = Mockery::mock(Address::class, [
            'toShipmentAddress' => Mockery::mock(ShipmentAddress::class, [
                'toArray' => [],
            ]),
        ]);

        $factory = new OrderFactory(
            $this->addressesGatewayMock($addressMock),
            $this->orderLineFactoryMock(
                $this->orderLineMock(
                    $orderLineAmount / 100,
                    $orderLineDescription,
                    $orderCurrencyCode,
                    (float) $orderLineQuantity,
                    $this->itemMock($this->weightMock($itemWeight), $itemPictureUrl)
                )
            )
        );

        $responseCreatedTimestamp = $createdAt * 1000;

        $order = $factory->createFromArray([
            'OrderID'                   => $orderId,
            'Created'                   => "/Date(${responseCreatedTimestamp})/",
            'Description'               => $orderDescription,
            'ShippingMethodDescription' => $orderShippingMethodDescription,
            'AmountFC'                  => $orderAmount / 100,
            'Currency'                  => $orderCurrencyCode,
            'DeliveryAddress'           => $faker->uuid,
            'SalesOrderLines'           => [
                'results' => [
                    [
                        'AmountFC'        => $orderLineAmount / 100,
                        'Description'     => $orderLineDescription,
                        'ItemDescription' => $orderLineItemDescription,
                        'Quantity'        => $orderLineQuantity,
                        'Item'            => $faker->uuid,
                    ],
                ],
            ],
        ]);

        $shopIdUuid = $faker->uuid;
        $shopIdMock = Mockery::mock(ShopId::class, [
            'toString' => $shopIdUuid,
        ]);

        self::assertEquals([
            'type'          => 'shipments',
            'attributes'    => [
                'created_at'          => $createdAt,
                'description'         => $orderDescription,
                'customer_reference'  => $orderId,
                'channel'             => 'test',
                'total_value'         => [
                    'amount'   => $orderAmount,
                    'currency' => $orderCurrencyCode,
                ],
                'price'               => [
                    'amount'   => $orderAmount,
                    'currency' => $orderCurrencyCode,
                ],
                'physical_properties' => [
                    'weight' => $itemWeight,
                ],
                'items'               => [
                    [
                        'description' => $orderLineDescription,
                        'image_url'   => $itemPictureUrl,
                        'item_value'  => [
                            'amount'   => $orderLineAmount,
                            'currency' => $orderCurrencyCode,
                        ],
                        'quantity'    => $orderLineQuantity,
                        'item_weight' => $itemWeight,
                    ],
                ],
                'tags'                => [
                    $orderShippingMethodDescription,
                ],
            ],
            'relationships' => [
                'shop' => [
                    'data' => [
                        'type' => 'shops',
                        'id'   => $shopIdUuid,
                    ],
                ],
            ],
        ], $order->toShipment($shopIdMock, 'test')->transformToJsonApiArray());
    }

    private function addressesGatewayMock(Address $addressMock): AddressesGateway|MockInterface
    {
        return Mockery::mock(AddressesGateway::class, [
            'fetchOneByAddressId' => $addressMock,
        ]);
    }

    private function orderLineFactoryMock(MockInterface|OrderLine $orderLineMock): OrderLineFactory|MockInterface
    {
        return Mockery::mock(OrderLineFactory::class, [
            'createFromArray' => $orderLineMock,
        ]);
    }

    private function orderLineMock(
        ?float $orderLineAmount,
        ?string $orderLineDescription,
        ?string $orderCurrencyCode,
        ?float $orderLineQuantity,
        MockInterface|Item $itemMock
    ): OrderLine|MockInterface {
        return Mockery::mock(OrderLine::class, [
            'toShipmentItem' => Mockery::mock(ShipmentItem::class, [
                'toArray' => [
                    'description' => $orderLineDescription,
                    'image_url'   => $itemMock->getPictureUrl(),
                    'item_value'  => [
                        'amount'   => (int) bcmul('100', (string) $orderLineAmount),
                        'currency' => $orderCurrencyCode,
                    ],
                    'quantity'    => (int) $orderLineQuantity,
                    'item_weight' => $itemMock->getWeight()->toGrams(),
                ],
            ]),
            'getItem'        => $itemMock,
        ]);
    }

    private function emptyOrderLineMock(
        MockInterface|Item $itemMock
    ): OrderLine|MockInterface {
        return Mockery::mock(OrderLine::class, [
            'toShipmentItem' => Mockery::mock(ShipmentItem::class, [
                'toArray' => [],
            ]),
            'getItem'        => $itemMock,
        ]);
    }

    private function itemMock(MockInterface|Weight $weightMock, ?string $itemPictureUrl): Item|MockInterface
    {
        return Mockery::mock(Item::class, [
            'getDescription' => Factory::create()->text,
            'getWeight'      => $weightMock,
            'getPictureUrl'  => $itemPictureUrl,
        ]);
    }

    private function weightMock(?int $itemWeight): Weight|MockInterface
    {
        return Mockery::mock(Weight::class, [
            'toGrams' => (int) $itemWeight,
        ]);
    }

    public function test_should_create_order_from_array_with_nulls(): void
    {
        $faker = Factory::create();

        $factory = new OrderFactory(
            $this->addressesGatewayMock(Mockery::mock(Address::class, [
                'toShipmentAddress' => Mockery::mock(ShipmentAddress::class, [
                    'toArray' => [],
                ]),
            ])),
            $this->orderLineFactoryMock(
                $this->emptyOrderLineMock(
                    $this->itemMock($this->weightMock(null), null)
                )
            )
        );

        $order = $factory->createFromArray([
            'OrderID'                   => null,
            'Created'                   => null,
            'Description'               => null,
            'ShippingMethodDescription' => null,
            'AmountFC'                  => null,
            'Currency'                  => null,
            'DeliveryAddress'           => null,
            'SalesOrderLines'           => [
                'results' => [
                    [
                        'AmountFC'        => null,
                        'Description'     => null,
                        'ItemDescription' => null,
                        'Quantity'        => null,
                        'Item'            => null,
                    ],
                ],
            ],
        ]);

        $shopIdUuid = $faker->uuid;
        $shopIdMock = Mockery::mock(ShopId::class, [
            'toString' => $shopIdUuid,
        ]);

        self::assertEquals([
            'type'          => 'shipments',
            'attributes'    => [
                'channel' => 'test',
            ],
            'relationships' => [
                'shop' => [
                    'data' => [
                        'type' => 'shops',
                        'id'   => $shopIdUuid,
                    ],
                ],
            ],
        ], $order->toShipment($shopIdMock, 'test')->transformToJsonApiArray());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
