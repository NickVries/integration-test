<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Item;

use App\Http\ExactApiDivisionClient;
use App\Shipments\Domain\Item\Item;
use App\Shipments\Domain\Item\ItemFactory;
use App\Shipments\Domain\Item\ItemsGateway;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Utils;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\UuidInterface;

class ItemsGatewayTest extends TestCase
{
    /**
     * @throws GuzzleException
     */
    public function test_should_get_cached_item_object(): void
    {
        $itemMock = Mockery::mock(Item::class);
        $clientMock = Mockery::mock(ExactApiDivisionClient::class);
        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('getBody')->once()->andReturn(Utils::jsonEncode([]));
        $clientMock->shouldReceive('get')->once()->andReturn($responseMock);
        $itemFactoryMock = Mockery::mock(ItemFactory::class, [
            'createFromArray' => $itemMock,
        ]);

        $gateway = new ItemsGateway($clientMock, $itemFactoryMock);

        $uuidMock = Mockery::mock(UuidInterface::class, ['toString' => 'test']);

        $gateway->fetchOneByItemId($uuidMock);
        $item = $gateway->fetchOneByItemId($uuidMock);

        self::assertSame($itemMock, $item);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
