<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Authentication\AuthServer;
use App\Authentication\AuthServerInterface;
use Faker\Factory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Utils;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_should_authenticate(): void
    {
        $faker = Factory::create();

        $this->app->singleton(AuthServerInterface::class, fn() => new AuthServer(
            Mockery::mock(Client::class, [
                'post' => Mockery::mock(ResponseInterface::class, [
                    'getBody' => Utils::jsonEncode([
                        'refresh_token' => $faker->text,
                        'access_token'  => $faker->text,
                        'expires_in'    => 600,
                        'token_type'    => 'bearer',
                    ]),
                ]),
            ]),
            $faker->uuid,
            $faker->password,
            $faker->url,
        ));

        $shopId = $faker->uuid;
        $response = $this->post('/public/authenticate', [
            'shop_id' => $shopId,
            'code'    => $faker->text,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tokens', [
            'shop_id' => $shopId,
        ]);
    }

    public function test_should_fail_authentication_upon_unknown_bad_request(): void
    {
        $faker = Factory::create();

        $clientMock = Mockery::mock(Client::class);
        $clientMock->shouldReceive('post')->andThrow(
            Mockery::mock(RequestException::class, [
                'getResponse' => null,
            ])
        );
        $this->app->singleton(AuthServerInterface::class, fn() => new AuthServer(
            $clientMock,
            $faker->uuid,
            $faker->password,
            $faker->url,
        ));

        $shopId = $faker->uuid;
        $response = $this->post('/public/authenticate', [
            'shop_id' => $shopId,
            'code'    => $faker->text,
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'errors' => [
                [
                    'status' => '400',
                    'title'  => 'Authentication error',
                    'detail' => 'Unknown request exception',
                ],
            ],
        ]);

        $this->assertDatabaseMissing('tokens', [
            'shop_id' => $shopId,
        ]);
    }

    public function test_should_fail_authentication_upon_precise_bad_request(): void
    {
        $faker = Factory::create();

        $clientMock = Mockery::mock(Client::class);
        $clientMock->shouldReceive('post')->andThrow(
            Mockery::mock(RequestException::class, [
                'getResponse' => Mockery::mock(ResponseInterface::class, [
                    'getBody'       => Utils::jsonEncode([
                        'error'             => 'test_error',
                        'error_description' => 'Testing errors',
                    ]),
                    'getStatusCode' => 400,
                ]),
            ])
        );
        $this->app->singleton(AuthServerInterface::class, fn() => new AuthServer(
            $clientMock,
            $faker->uuid,
            $faker->password,
            $faker->url,
        ));

        $shopId = $faker->uuid;
        $response = $this->post('/public/authenticate', [
            'shop_id' => $shopId,
            'code'    => $faker->text,
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'errors' => [
                [
                    'status' => '400',
                    'title'  => 'test_error',
                    'detail' => 'Testing errors',
                ],
            ],
        ]);

        $this->assertDatabaseMissing('tokens', [
            'shop_id' => $shopId,
        ]);
    }
}
