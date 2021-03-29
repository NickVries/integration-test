<?php
/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Address;

use App\Shipments\Domain\Address\Address;
use App\Shipments\Domain\Address\AddressesGateway;
use App\Shipments\Domain\Address\AddressFactory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Utils;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\UuidInterface;

class AddressesGatewayTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @throws GuzzleException
     */
    public function test_should_get_fresh_address_object(): void
    {
        $addressMock = Mockery::mock(Address::class);
        $clientMock = Mockery::mock(Client::class);
        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('getBody')->once()->andReturn(Utils::jsonEncode([]));
        $clientMock->shouldReceive('get')->once()->andReturn($responseMock);
        $addressFactoryMock = Mockery::mock(AddressFactory::class, [
            'createFromArray' => $addressMock,
        ]);

        $gateway = new AddressesGateway($addressFactoryMock, Mockery::mock(CacheInterface::class, [
            'has' => false,
            'set' => null,
        ]));

        $uuidMock = Mockery::mock(UuidInterface::class, ['toString' => 'test']);

        $address = $gateway->fetchOneByAddressId($uuidMock, $clientMock);

        self::assertSame($addressMock, $address);
    }

    public function test_should_get_cached_address_object(): void
    {
        $addressMock = Mockery::mock(Address::class);
        $clientMock = Mockery::mock(Client::class);
        $addressFactoryMock = Mockery::mock(AddressFactory::class);

        $gateway = new AddressesGateway($addressFactoryMock, Mockery::mock(CacheInterface::class, [
            'has' => true,
            'get' => $addressMock,
        ]));

        $uuidMock = Mockery::mock(UuidInterface::class, ['toString' => 'test']);

        $gateway->fetchOneByAddressId($uuidMock, $clientMock);
        $address = $gateway->fetchOneByAddressId($uuidMock, $clientMock);

        self::assertSame($addressMock, $address);
    }



    public function test_should_get_no_addresses_by_date_range(): void
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
        $addressFactoryMock = Mockery::mock(AddressFactory::class);

        $gateway = new AddressesGateway($addressFactoryMock, Mockery::mock(CacheInterface::class));

        $dateMock = Mockery::mock(Carbon::class, [
            'toIso8601ZuluString' => '',
        ]);

        $addresses = $gateway->fetchByDateRange($dateMock, $dateMock, $clientMock);

        self::assertCount(0, $addresses);
    }

    public function test_should_get_addresses_by_date_range(): void
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
        $addressFactoryMock = Mockery::mock(AddressFactory::class, [
            'createFromArray' => Mockery::mock(Address::class)
        ]);

        $gateway = new AddressesGateway($addressFactoryMock, Mockery::mock(CacheInterface::class));

        $dateMock = Mockery::mock(Carbon::class, [
            'toIso8601ZuluString' => '',
        ]);

        $addresses = $gateway->fetchByDateRange($dateMock, $dateMock, $clientMock);

        self::assertCount(1, $addresses);
    }
}
