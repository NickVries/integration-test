<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\Domain;

use App\Authentication\Domain\AuthorizationSession;
use App\Authentication\Domain\Exceptions\AuthSessionExpiredException;
use App\Authentication\Domain\ShopId;
use Faker\Factory;
use Illuminate\Cache\Repository;
use Mockery;
use PHPUnit\Framework\TestCase;

class AuthorizationSessionTest extends TestCase
{
    public function test_should_save_shop_id_and_redirect_uri_to_cache(): void
    {
        $this->expectNotToPerformAssertions();

        $authSession = new AuthorizationSession(Mockery::mock(Repository::class, [
            'put' => null,
        ]));

        $faker = Factory::create();
        $authSession->save(
            Mockery::mock(ShopId::class, [
                'toString' => $faker->uuid,
            ]),
            $faker->uuid
        );
    }

    public function test_should_fetch_shop_id_and_redirect_uri_from_token(): void
    {
        $faker = Factory::create();

        $shopId = $faker->uuid;
        $redirectUri = $faker->url;

        $authSession = new AuthorizationSession(Mockery::mock(Repository::class, [
            'pull' => [
                'shop_id'      => $shopId,
                'redirect_uri' => $redirectUri,
            ],
            'has'  => true,
        ]));

        $payload = $authSession->fetch($faker->uuid);

        self::assertInstanceOf(ShopId::class, $payload['shop_id']);
        self::assertEquals($shopId, $payload['shop_id']->toString());
        self::assertEquals($redirectUri, $payload['redirect_uri']);
    }

    public function test_should_throw_exception_when_session_has_expired(): void
    {
        $this->expectException(AuthSessionExpiredException::class);

        $faker = Factory::create();

        $authSession = new AuthorizationSession(Mockery::mock(Repository::class, [
            'has' => false,
        ]));

        $authSession->fetch($faker->uuid);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
