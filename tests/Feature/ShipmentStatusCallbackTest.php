<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Authentication\Domain\ExpiresAt;
use App\Authentication\Domain\Token;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Tests\TestCase;

class ShipmentStatusCallbackTest extends TestCase
{
    /** @test */
    public function test_should_update_status_tracking(): void
    {
        // TODO Mock the remote API. See https://docs.guzzlephp.org/en/stable/testing.html
        $token = $this->createActiveToken();

        $requestStub = file_get_contents(base_path('tests/Stubs/status-request.json'));
        $data = json_decode($requestStub, true);
        Arr::set($data, 'included.1.relationships.shop.data.id', $token->shop_id->toString());

        $response = $this->post(
            '/callback/shipment-statuses',
            $data
        );

        $response->assertStatus(200);
    }

    private function createActiveToken(): Token
    {
        return Token::factory()->create([
            'expires_at' => new ExpiresAt(Carbon::now()->addSeconds(600)),
        ]);
    }
}
