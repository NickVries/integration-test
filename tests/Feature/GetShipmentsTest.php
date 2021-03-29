<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Feature;

use App\Authentication\Domain\ExpiresAt;
use App\Authentication\Domain\Token;
use App\Http\ExactApiClient;
use Carbon\Carbon;
use Faker\Factory;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use function json_encode;
use function random_int;

class GetShipmentsTest extends TestCase
{
    public function test_should_get_400_bad_request_when_shop_id_is_missing(): void
    {
        $response = $this->get('/shipments');

        $response->assertStatus(400);
    }

    public function test_should_get_422_unprocessable_entity_when_shop_id_is_not_valid_uuid(): void
    {
        $response = $this->get('/shipments?shop_id=1234567');

        $response->assertStatus(422);
    }

    public function test_should_get_401_unauthorized_when_shop_is_not_authenticated(): void
    {
        $response = $this->get('/shipments?shop_id=' . Factory::create()->uuid);

        $response->assertStatus(401);
    }

    public function test_should_get_200_ok_with_available_token(): void
    {
        $token = $this->createActiveToken();

        ExactApiClient::setHandler(HandlerStack::create(new MockHandler([
            $this->divisionResponse(),
            new Response(200, [], json_encode([])),
        ])));

        $response = $this->get(
            '/shipments?shop_id='
            . $token->shop_id
            . '&filter[start_date]=2020-01-01'
            . '&filter[end_date]=2022-01-01'
        );

        $response->assertStatus(200);
    }

    private function createActiveToken(): Token
    {
        return Token::factory()->create([
            'expires_at' => new ExpiresAt(Carbon::now()->addSeconds(600)),
        ]);
    }

    public function test_should_get_one_shipment(): void
    {
        $token = $this->createActiveToken();

        $faker = Factory::create();

        $orderId = $faker->uuid;
        $description = $faker->text(30);
        $shippingMethod = $faker->word;
        $totalAmount = random_int(100, 999);
        $currencyCode = $faker->currencyCode;
        $quantity = random_int(1, 10);
        $itemDescription = $faker->text;
        $createdAt = $faker->unixTime;
        $responseCreatedAt = $createdAt * 1000;

        $salesOrdersResponse = new Response(200, [], json_encode([
            'd' => [
                'results' => [
                    [
                        'OrderID'                   => $orderId,
                        'Created'                   => "/Date(${responseCreatedAt})/",
                        'Description'               => $description,
                        'ShippingMethodDescription' => $shippingMethod,
                        'AmountFC'                  => $totalAmount / 100,
                        'Currency'                  => $currencyCode,
                        'DeliveryAddress'           => $faker->uuid,
                        'SalesOrderLines'           => [
                            'results' => [
                                [
                                    'AmountFC'        => $totalAmount / 100,
                                    'Description'     => $itemDescription,
                                    'ItemDescription' => $faker->text,
                                    'Quantity'        => (float) $quantity,
                                    'Item'            => $faker->uuid,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $company = $faker->company;
        $phoneNumber = $faker->phoneNumber;
        $countryCode = $faker->countryCode;
        $city = $faker->city;
        $postcode = $faker->postcode;
        $streetName = $faker->streetName;
        $streetNumber = random_int(1, 99);

        $deliverAddressResponse = new Response(200, [], json_encode([
            'd' => [
                'ID'           => $faker->uuid,
                'ContactName'  => "${firstName} ${lastName}",
                'Country'      => $countryCode,
                'Postcode'     => $postcode,
                'AddressLine1' => "${streetName} ${streetNumber}",
                'AddressLine2' => null,
                'AddressLine3' => null,
                'City'         => $city,
                'AccountName'  => $company,
                'Phone'        => $phoneNumber,
            ],
        ], JSON_THROW_ON_ERROR));

        $weight = random_int(1, 100);
        $pictureUrl = $faker->imageUrl();

        $itemResponse = new Response(200, [], json_encode([
            'd' => [
                'ID'            => $faker->uuid,
                'Description'   => $faker->text,
                'NetWeight'     => $weight,
                'NetWeightUnit' => 'g',
                'PictureUrl'    => $pictureUrl,
            ],
        ], JSON_THROW_ON_ERROR));

        ExactApiClient::setHandler(HandlerStack::create(new MockHandler([
            $this->divisionResponse(),
            $salesOrdersResponse,
            $deliverAddressResponse,
            $itemResponse,
        ])));

        $response = $this->get(
            '/shipments?shop_id='
            . $token->shop_id
            . '&filter[start_date]=2020-01-01'
            . '&filter[end_date]=2022-01-01'
        );

        $response->assertStatus(200);


        $response->assertExactJson([
            'data' => [
                [
                    'type'          => 'shipments',
                    'attributes'    => [
                        'recipient_address'   => [
                            'street_1'      => $streetName,
                            'street_number' => $streetNumber,
                            'postal_code'   => $postcode,
                            'city'          => $city,
                            'country_code'  => $countryCode,
                            'first_name'    => $firstName,
                            'last_name'     => $lastName,
                            'company'       => $company,
                            'phone_number'  => $phoneNumber,
                        ],
                        'description'         => $description,
                        'created_at'          => $createdAt,
                        'customer_reference'  => $orderId,
                        'channel'             => 'test',
                        'total_value'         => [
                            'amount'   => $totalAmount,
                            'currency' => $currencyCode,
                        ],
                        'price'               => [
                            'amount'   => $totalAmount,
                            'currency' => $currencyCode,
                        ],
                        'physical_properties' => [
                            'weight' => $weight,
                        ],
                        'items'               => [
                            [
                                'description' => $itemDescription,
                                'image_url'   => $pictureUrl,
                                'item_value'  => [
                                    'amount'   => $totalAmount,
                                    'currency' => $currencyCode,
                                ],
                                'quantity'    => $quantity,
                                'item_weight' => $weight,
                            ],
                        ],
                        'tags'                => [
                            $shippingMethod,
                        ],
                    ],
                    'relationships' => [
                        'shop' => [
                            'data' => [
                                'type' => 'shops',
                                'id'   => $token->shop_id->toString(),
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        ExactApiClient::setHandler(HandlerStack::create(new MockHandler([
            $this->divisionResponse(),
        ])));
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
}
