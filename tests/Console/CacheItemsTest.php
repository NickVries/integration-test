<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Console;

use App\Authentication\Domain\Token;
use App\Http\ExactApiClient;
use App\Shipments\Domain\Item\Item;
use Faker\Factory;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use function json_encode;
use function random_int;

class CacheItemsTest extends TestCase
{
    public function test_it_caches_address_entries(): void
    {
        $itemID = Factory::create()->uuid;

        ExactApiClient::setHandler(HandlerStack::create(
            new MockHandler([
                $this->divisionResponse(),
                $this->itemResponse($itemID),
            ])
        ));

        Token::factory()->create();

        $this
            ->artisan('exact:cache:items')
            ->assertExitCode(0);

        self::assertTrue(Cache::has(Item::generateCacheKey($itemID)));
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

    private function itemResponse(string $itemID): Response
    {
        $faker = Factory::create();
        $description = $faker->text;
        $grossWeight = random_int(10, 99);
        $units = ['kg', 'g'];
        $netWeightUnit = $units[array_rand($units)];
        $pictureUrl = $faker->imageUrl();

        return new Response(200, [], json_encode([
            'd' => [
                'results' => [
                    [
                        'ID'            => $itemID,
                        'Description'   => $description,
                        'GrossWeight'   => $grossWeight,
                        'NetWeightUnit' => $netWeightUnit,
                        'PictureUrl'    => $pictureUrl,
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
