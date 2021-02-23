<?php
/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Address;

use App\Http\ExactApiDivisionClient;
use App\Shipments\Domain\Address\Address;
use App\Shipments\Domain\Address\AddressesGateway;
use App\Shipments\Domain\Address\AddressFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Utils;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\UuidInterface;

class AddressesGatewayTest extends TestCase
{
    /**
     * @throws GuzzleException
     */
    public function test_should_get_cached_address_object(): void
    {
        $addressMock = Mockery::mock(Address::class);
        $clientMock = Mockery::mock(ExactApiDivisionClient::class);
        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('getBody')->once()->andReturn(Utils::jsonEncode([]));
        $clientMock->shouldReceive('get')->once()->andReturn($responseMock);
        $addressFactoryMock = Mockery::mock(AddressFactory::class, [
            'createFromArray' => $addressMock,
        ]);

        $gateway = new AddressesGateway($clientMock, $addressFactoryMock);

        $uuidMock = Mockery::mock(UuidInterface::class, ['toString' => 'test']);

        $gateway->fetchOneByAddressId($uuidMock);
        $address = $gateway->fetchOneByAddressId($uuidMock);

        self::assertSame($addressMock, $address);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
