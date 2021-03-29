<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Item;

use App\Shipments\Domain\Item\Item;
use App\Shipments\Domain\Item\ItemFactory;
use App\Shipments\Domain\Item\ItemsGateway;
use Carbon\Carbon;
use Faker\Factory;
use GuzzleHttp\Client;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\UuidInterface;
use function json_encode;

class ItemsGatewayTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function test_should_get_cached_item_object(): void
    {
        $itemMock = Mockery::mock(Item::class);
        $clientMock = Mockery::mock(Client::class);
        $itemFactoryMock = Mockery::mock(ItemFactory::class);

        $gateway = new ItemsGateway($itemFactoryMock, Mockery::mock(CacheInterface::class, [
            'has' => true,
            'get' => $itemMock,
        ]));

        $uuidMock = Mockery::mock(UuidInterface::class, ['toString' => 'test']);

        $gateway->fetchOneByItemId($uuidMock, $clientMock);
        $item = $gateway->fetchOneByItemId($uuidMock, $clientMock);

        self::assertSame($itemMock, $item);
    }

    public function test_should_get_no_items_by_date_range(): void
    {
        $clientMock = Mockery::mock(Client::class, [
            'get' => Mockery::mock(ResponseInterface::class, [
                'getBody' => json_encode([
                    'd' => [
                        'results' => [],
                    ],
                ], JSON_THROW_ON_ERROR),
            ]),
        ]);
        $itemFactoryMock = Mockery::mock(ItemFactory::class);

        $gateway = new ItemsGateway($itemFactoryMock, Mockery::mock(CacheInterface::class));

        $dateMock = Mockery::mock(Carbon::class, [
            'toIso8601ZuluString' => '',
        ]);

        $items = $gateway->fetchByDateRange($dateMock, $dateMock, $clientMock);

        self::assertCount(0, $items);
    }

    public function test_should_get_items_by_date_range(): void
    {
        $clientMock = Mockery::mock(Client::class, [
            'get' => Mockery::mock(ResponseInterface::class, [
                'getBody' => json_encode([
                    'd' => [
                        'results' => [[]]
                    ]
                ], JSON_THROW_ON_ERROR),
            ]),
        ]);
        $itemFactoryMock = Mockery::mock(ItemFactory::class, [
            'createFromArray' => Mockery::mock(Item::class)
        ]);

        $gateway = new ItemsGateway($itemFactoryMock, Mockery::mock(CacheInterface::class));

        $dateMock = Mockery::mock(Carbon::class, [
            'toIso8601ZuluString' => '',
        ]);

        $items = $gateway->fetchByDateRange($dateMock, $dateMock, $clientMock);

        self::assertCount(1, $items);
    }
}
