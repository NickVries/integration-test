<?php
/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Account;

use App\Shipments\Domain\Account\Account;
use App\Shipments\Domain\Account\AccountFactory;
use App\Shipments\Domain\Account\AccountsGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Utils;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\UuidInterface;

class AccountsGatewayTest extends TestCase
{
    /**
     * @throws GuzzleException
     */
    public function test_should_get_fresh_account_object(): void
    {
        $accountMock = Mockery::mock(Account::class);
        $clientMock = Mockery::mock(Client::class);
        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('getBody')->once()->andReturn(Utils::jsonEncode([]));
        $clientMock->shouldReceive('get')->once()->andReturn($responseMock);
        $accountFactoryMock = Mockery::mock(AccountFactory::class, [
            'createFromArray' => $accountMock,
        ]);

        $gateway = new AccountsGateway($accountFactoryMock, Mockery::mock(CacheInterface::class, [
            'has' => false,
            'set' => null,
        ]));

        $uuidMock = Mockery::mock(UuidInterface::class, ['toString' => 'test']);

        $account = $gateway->fetchOneByAccountId($uuidMock, $clientMock);

        self::assertSame($accountMock, $account);
    }

    public function test_should_get_cached_account_object(): void
    {
        $accountMock = Mockery::mock(Account::class);
        $clientMock = Mockery::mock(Client::class);
        $accountFactoryMock = Mockery::mock(AccountFactory::class);

        $gateway = new AccountsGateway($accountFactoryMock, Mockery::mock(CacheInterface::class, [
            'has' => true,
            'get' => $accountMock,
        ]));

        $uuidMock = Mockery::mock(UuidInterface::class, ['toString' => 'test']);

        $gateway->fetchOneByAccountId($uuidMock, $clientMock);
        $account = $gateway->fetchOneByAccountId($uuidMock, $clientMock);

        self::assertSame($accountMock, $account);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
