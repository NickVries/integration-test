<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Item;

use App\Shipments\Domain\LoadAndCache;
use App\Shipments\Domain\MakeRequest;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use ODataQuery\Filter\Operators\Logical\ODataAndOperator;
use ODataQuery\Filter\Operators\Logical\ODataGreaterThanOperator;
use ODataQuery\Filter\Operators\Logical\ODataLessThanOperator;
use ODataQuery\ODataResourcePath;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\UuidInterface;
use function array_map;

class ItemsGateway
{
    use MakeRequest;
    use LoadAndCache;

    private const ENTITY = 'logistics/Items';

    private const FILTER_DATE_FIELD = 'Created';

    public function __construct(
        private ItemFactory $itemFactory,
        private CacheInterface $cache
    ) {
    }

    public function fetchOneByItemId(UuidInterface $id, Client $client): Item
    {
        $cacheKey = Item::generateCacheKey($id->toString());
        $resolver = function () use ($client, $id): Item {
            try {
                $response = $this->request(new ODataResourcePath(self::ENTITY . "(guid'${id}')"), $client);
            } catch (GuzzleException $e) {
                return $this->itemFactory->createNullItem();
            }
            return $this->itemFactory->createFromArray((array) Arr::get($response, 'd', []));
        };

        return $this->loadCached($cacheKey, $resolver, Item::getCacheTtl(), $this->cache);
    }

    public function fetchByDateRange(Carbon $start, Carbon $end, Client $client): array
    {
        $path = new ODataResourcePath(self::ENTITY);

        $path->setFilter(new ODataAndOperator(
            new ODataGreaterThanOperator(self::FILTER_DATE_FIELD, "datetime'{$start->toIso8601ZuluString()}'"),
            new ODataLessThanOperator(self::FILTER_DATE_FIELD, "datetime'{$end->toIso8601ZuluString()}'"),
        ));

        try {
            $response = $this->request($path, $client);
        } catch (GuzzleException $e) {
            return [];
        }

        return array_map(
            fn(array $item) => $this->itemFactory->createFromArray($item),
            Arr::get($response, 'd.results', [])
        );
    }
}
