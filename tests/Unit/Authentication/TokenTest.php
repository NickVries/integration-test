<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication;

use App\Authentication\AuthServerInterface;
use App\Authentication\ExpiresAt;
use App\Authentication\ExpiresIn;
use App\Authentication\ShopId;
use App\Authentication\Token;
use App\Authentication\TokenType;
use Carbon\Carbon;
use Faker\Factory;
use Mockery;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function test_should_create_token_with_shop_id_access_token_refresh_token_expires_in_token_type(): void
    {
        $factory = Factory::create();

        $shopIdMock = Mockery::mock(ShopId::class, [
            'toString' => $factory->uuid,
        ]);

        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(Token::class, Token::create($shopIdMock));
    }

    public function test_should_get_existing_access_token_if_not_expired(): void
    {
        $faker = Factory::create();

        $shopIdMock = Mockery::mock(ShopId::class, [
            'toString' => $faker->uuid,
        ]);
        $tokenTypeMock = Mockery::mock(TokenType::class, ['getValue' => 'bearer']);

        $expiresInMock = Mockery::mock(ExpiresIn::class, [
            'toSeconds'   => 600,
        ]);

        $accessToken = $faker->text;
        $token = Token::create($shopIdMock);
        $expiresAtMock = Mockery::mock(ExpiresAt::class, [
            'toDateTimeString' => Carbon::now()->addSeconds(600)->toDateTimeString(),
            'hasExpired'       => false,
        ]);
        $token->fill([
            'access_token' => $accessToken,
            'refresh_token' => $faker->text,
            'token_type' => $tokenTypeMock,
            'expires_in' => $expiresInMock,
            'expires_at' => $expiresAtMock,
        ]);
        $newAccessToken = $token->obtainAccessToken(Mockery::mock(AuthServerInterface::class));

        self::assertSame($accessToken, $newAccessToken);
    }

    public function test_should_get_new_access_token_if_expired(): void
    {
        $faker = Factory::create();

        $shopIdMock = Mockery::mock(ShopId::class, [
            'toString' => $faker->uuid,
        ]);
        $tokenTypeMock = Mockery::mock(TokenType::class, ['getValue' => 'bearer']);

        $expiresInMock = Mockery::mock(ExpiresIn::class, [
            'toSeconds'   => 600,
        ]);

        $expiresAtMock = Mockery::mock(ExpiresAt::class, [
            'toDateTimeString' => Carbon::now()->addSeconds(600)->toDateTimeString(),
            'hasExpired'       => true,
        ]);

        $token = Token::create($shopIdMock);
        $token->fill([
            'access_token' => $faker->text,
            'refresh_token' => $faker->text,
            'token_type' => $tokenTypeMock,
            'expires_in' => $expiresInMock,
            'expires_at' => $expiresAtMock,
        ]);

        $newRefreshToken = $faker->text;
        $newAccessToken = $faker->text;

        $newExpiresAt = Mockery::mock(ExpiresAt::class, [
            'toDateTimeString' => Carbon::now()->addSeconds(600)->toDateTimeString(),
        ]);
        $newExpiresIn = Mockery::mock(ExpiresIn::class, [
            'toSeconds'   => 600,
            'toExpiresAt' => $newExpiresAt,
        ]);
        $authServerClientMock = Mockery::mock(AuthServerInterface::class, [
            'refreshToken' => [
                'access_token'  => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'expires_in'    => $newExpiresIn,
                'token_type'    => $tokenTypeMock,
            ],
        ]);

        self::assertSame($newAccessToken, $token->obtainAccessToken($authServerClientMock));
    }
}
