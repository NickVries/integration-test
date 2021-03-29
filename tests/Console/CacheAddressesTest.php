<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Console;

use App\Authentication\Domain\Token;
use App\Http\ExactApiClient;
use App\Shipments\Domain\Address\Address;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;
use function json_encode;
use function random_int;
use function strtoupper;

class CacheAddressesTest extends TestCase
{
    public function test_it_caches_address_entries(): void
    {
        $addressID = Factory::create()->uuid;

        ExactApiClient::setHandler(HandlerStack::create(
            new MockHandler([
                $this->divisionResponse(),
                $this->addressResponse($addressID),
                $this->accountResponse(),
            ])
        ));

        Token::factory()->create();

        $this
            ->artisan('exact:cache:addresses')
            ->assertExitCode(0);

        self::assertTrue(Cache::has(Address::generateCacheKey($addressID)));
    }

    private function divisionResponse(): Response
    {
        return new Response(200, [], json_encode([
            'd' => [
                'results' => [
                    [
                        'CurrentDivision' => '123',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        ExactApiClient::setHandler(null);
    }

    private function addressResponse(string $addressID): Response
    {
        $faker = Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $countryCode = $faker->countryCode;
        $postcode = $faker->postcode;
        $stateCode = strtoupper(Str::random(2));
        $streetNumber = random_int(10, 99);
        $streetName = $faker->streetName;
        $address2 = $faker->streetAddress;
        $address3 = $faker->streetAddress;
        $city = $faker->city;
        $phoneNumber = $faker->phoneNumber;

        return new Response(200, [], json_encode([
            'd' => [
                'results' => [
                    [
                        'ID'           => $addressID,
                        'Account'      => $faker->uuid,
                        'Country'      => $countryCode,
                        'Postcode'     => $postcode,
                        'State'        => $stateCode,
                        'AddressLine1' => $streetName . ' ' . $streetNumber . 'A',
                        'AddressLine2' => $address2,
                        'AddressLine3' => $address3,
                        'City'         => $city,
                        'AccountName'  => $firstName . ' ' . $lastName,
                        'Phone'        => $phoneNumber,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));
    }

    private function accountResponse(): Response
    {
        return new Response(200, [], json_encode([
            'd' => [
                'Email' => Factory::create()->email,
            ],
        ], JSON_THROW_ON_ERROR));
    }
}
