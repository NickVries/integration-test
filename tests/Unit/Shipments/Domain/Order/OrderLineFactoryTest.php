<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Order;

use App\Shipments\Domain\Item\Item;
use App\Shipments\Domain\Item\ItemsGateway;
use App\Shipments\Domain\Item\Weight;
use App\Shipments\Domain\Order\OrderLineFactory;
use Faker\Factory;
use Mockery;
use PHPUnit\Framework\TestCase;
use function random_int;

class OrderLineFactoryTest extends TestCase
{
    public function test_should_create_order_line_from_array(): void
    {
        $faker = Factory::create();

        $factory = new OrderLineFactory(
            Mockery::mock(ItemsGateway::class, [
                'fetchOneByItemId' => Mockery::mock(Item::class, [
                    'getDescription' => $faker->text,
                    'getWeight'      => Mockery::mock(Weight::class, [
                        'toGrams' => random_int(0, 1000),
                    ]),
                    'getPictureUrl'  => $faker->imageUrl(),
                ]),
            ])
        );

        $amount = (float) random_int(0, 1000);
        $quantity = (float) random_int(0, 100);
        $description = $faker->text;
        $itemDescription = $faker->text;

        $orderLine = $factory->createFromArray([
            'AmountFC'        => $amount,
            'Description'     => $description,
            'ItemDescription' => $itemDescription,
            'Quantity'        => $quantity,
            'Item'            => $faker->uuid,
        ]);

        self::assertEquals($amount, $orderLine->getAmountFC());
        self::assertEquals($quantity, $orderLine->getQuantity());
        self::assertEquals($description, $orderLine->getDescription());
        self::assertEquals($itemDescription, $orderLine->getItemDescription());
    }

    public function test_should_create_order_line_from_array_with_nulls(): void
    {
        $factory = new OrderLineFactory(
            Mockery::mock(ItemsGateway::class, [
                'fetchOneByItemId' => Mockery::mock(Item::class, [
                    'getDescription' => null,
                    'getWeight'      => Mockery::mock(Weight::class, [
                        'toGrams' => 0,
                    ]),
                    'getPictureUrl'  => null,
                ]),
            ])
        );

        $orderLine = $factory->createFromArray([
            'AmountFC'        => null,
            'Description'     => null,
            'ItemDescription' => null,
            'Quantity'        => null,
            'Item'            => null,
        ]);

        self::assertNull($orderLine->getAmountFC());
        self::assertNull($orderLine->getQuantity());
        self::assertNull($orderLine->getDescription());
        self::assertNull($orderLine->getItemDescription());
    }
}
