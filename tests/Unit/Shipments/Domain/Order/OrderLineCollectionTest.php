<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Order;

use App\Shipments\Domain\Item\Item;
use App\Shipments\Domain\Item\Weight;
use App\Shipments\Domain\Order\OrderLine;
use App\Shipments\Domain\Order\OrderLineCollection;
use Faker\Factory;
use Mockery;
use PHPUnit\Framework\TestCase;
use function random_int;

class OrderLineCollectionTest extends TestCase
{
    public function test_should_convert_order_line_collection_to_array(): void
    {
        $faker = Factory::create();

        $amount = random_int(0, 1000);
        $description = $faker->text;
        $itemDescription = $faker->text;
        $quantity = random_int(0, 1000);
        $orderCurrency = $faker->currencyCode;
        $pictureUrl = $faker->imageUrl();
        $grams = random_int(1, 900);

        $weightMock = Mockery::mock(Weight::class, [
            'toGrams' => $grams,
        ]);

        $itemMock = Mockery::mock(Item::class, [
            'getDescription' => $faker->text,
            'getWeight'      => $weightMock,
            'getPictureUrl'  => $pictureUrl,
        ]);

        $collection = new OrderLineCollection([
            Mockery::mock(OrderLine::class, [
                'getAmountFC'        => (float) $amount,
                'getDescription'     => $description,
                'getItemDescription' => $itemDescription,
                'getQuantity'        => (float) $quantity,
                'getItem'            => $itemMock,
            ]),
        ]);

        self::assertEquals([
            [
                'description' => $description,
                'image_url'   => $pictureUrl,
                'item_value'  => [
                    'amount'   => $amount,
                    'currency' => $orderCurrency,
                ],
                'quantity'    => $quantity,
                'item_weight' => $grams,
            ],
        ], $collection->toJsonApiArray($orderCurrency));
    }
}
