<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Feature;

use App\Authentication\Domain\ExpiresAt;
use App\Authentication\Domain\Token;
use Carbon\Carbon;
use Faker\Factory;
use Tests\TestCase;

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
        // TODO Mock the remote API. See https://docs.guzzlephp.org/en/stable/testing.html
        $token = $this->createActiveToken();

        $response = $this->get(
            '/shipments?shop_id='
            . $token->shop_id
            . '&filter[start_date]=2020-01-01'
            . '&filter[end_date]=2022-01-01'
            . '&page_number=1'
            . '&page_size=10'
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
        // TODO Mock the remote API. See https://docs.guzzlephp.org/en/stable/testing.html

        $token = $this->createActiveToken();

        self::markTestSkipped('Write test after getting shipments is implemented');
    }

    public function test_should_get_shipments_with_page_and_size_parameters(): void
    {
        $token = $this->createActiveToken();

        $response = $this->get(
            '/shipments?shop_id='
            . $token->shop_id
            . '&filter[start_date]=2020-01-01'
            . '&filter[end_date]=2022-01-01'
            . '&page_number=1'
            . '&page_size=10'
        );

        $response->assertStatus(200);
    }
}
