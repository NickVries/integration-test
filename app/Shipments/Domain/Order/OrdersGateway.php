<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Order;

use App\Authentication\Domain\ShopId;
use App\Http\ExactApiDivisionClient;
use App\Shipments\Domain\LoadAndCache;
use App\Shipments\Domain\MakeRequest;
use Carbon\Carbon;
use DateInterval;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use ODataQuery\Expand\ODataQueryExpand;
use ODataQuery\Expand\ODataQueryExpandCollection;
use ODataQuery\Filter\Operators\Logical\ODataAndOperator;
use ODataQuery\Filter\Operators\Logical\ODataGreaterThanOperator;
use ODataQuery\Filter\Operators\Logical\ODataLessThanOperator;
use ODataQuery\ODataResourcePath;
use Psr\SimpleCache\CacheInterface;
use function array_map;

class OrdersGateway
{
    use MakeRequest;
    use LoadAndCache;

    private const ENTITY = 'salesorder/SalesOrders';
    private const FILTER_DATE_FIELD = 'Created';

    public function __construct(
        private ExactApiDivisionClient $client,
        private OrderFactory $orderFactory,
        private CacheInterface $cache
    ) {
    }

    /**
     * @param ShopId $shopId
     * @param Carbon $start
     * @param Carbon $end
     * @return Order[]
     * @throws GuzzleException
     */
    public function fetchByDateRange(ShopId $shopId, Carbon $start, Carbon $end): array
    {
        $cacheKey = "orders_${shopId}_{$start->getTimestamp()}_{$end->getTimestamp()}";
        $resolver = fn(): array => array_map(
            fn(array $order) => $this->orderFactory->createFromArray($order),
            Arr::get($this->request($this->createQuery($start, $end)), 'd.results', [])
        );
        $ttl = new DateInterval('PT10M');

        return $this->loadCached($cacheKey, $resolver, $ttl, $this->cache);
    }

    private function createQuery(Carbon $start, Carbon $end): ODataResourcePath
    {
        $path = new ODataResourcePath(self::ENTITY);

        $path->setFilter(new ODataAndOperator(
            new ODataGreaterThanOperator(self::FILTER_DATE_FIELD, "datetime'{$start->toIso8601ZuluString()}'"),
            new ODataLessThanOperator(self::FILTER_DATE_FIELD, "datetime'{$end->toIso8601ZuluString()}'"),
        ));

        $path->setExpand(new ODataQueryExpandCollection([
            new ODataQueryExpand('SalesOrderLines'),
        ]));

        return $path;
    }
}
