<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Order;

use App\Shipments\Domain\Item\Item;
use App\Shipments\Domain\Item\ItemsGateway;
use App\Shipments\Domain\Item\Weight;
use App\Shipments\Domain\Order\OrderLineFactory;
use Faker\Factory;
use GuzzleHttp\Client;
use Mockery;
use PHPUnit\Framework\TestCase;
use function bcdiv;
use function bcmul;
use function random_int;

class OrderLineFactoryTest extends TestCase
{
    public function test_should_create_order_line_from_array(): void
    {
        $faker = Factory::create();

        $imageUrl = $faker->imageUrl();
        $grams = random_int(1, 1000);
        $factory = new OrderLineFactory(
            Mockery::mock(ItemsGateway::class, [
                'fetchOneByItemId' => Mockery::mock(Item::class, [
                    'getDescription' => $faker->text,
                    'getWeight'      => Mockery::mock(Weight::class, [
                        'toGrams' => $grams,
                    ]),
                    'getPictureUrl'  => $imageUrl,
                ]),
            ])
        );

        $amount = (float) random_int(1000, 9000);
        $quantity = (float) random_int(1, 100);
        $description = $faker->text;
        $itemDescription = $faker->text;

        $orderLine = $factory->createFromArray([
            'AmountFC'        => $amount,
            'Description'     => $description,
            'ItemDescription' => $itemDescription,
            'Quantity'        => $quantity,
            'Item'            => $faker->uuid,
        ], Mockery::mock(Client::class));

        self::assertEquals([
            'description' => $description,
            'image_url' => $imageUrl,
            'item_value' => [
                'amount' => (int) bcmul((string) $amount, '100'),
            ],
            'quantity' => (int) $quantity,
            'item_weight' => $grams,
        ], $orderLine->toShipmentItem(null)->toArray());
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
        ], Mockery::mock(Client::class));

        self::assertEquals([], $orderLine->toShipmentItem(null)->toArray());
    }
}
