<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

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

class AddressesGateway
{
    use MakeRequest;
    use LoadAndCache;

    private const ENTITY = 'crm/Addresses';
    private const FILTER_DATE_FIELD = 'Created';

    public function __construct(
        private AddressFactory $addressFactory,
        private CacheInterface $cache
    ) {
    }

    public function fetchOneByAddressId(UuidInterface $id, Client $client): Address
    {
        $cacheKey = Address::generateCacheKey($id->toString());
        $resolver = function () use ($client, $id): Address {
            try {
                $response = $this->request(new ODataResourcePath(self::ENTITY . "(guid'${id}')"), $client);
            } catch (GuzzleException $e) {
                return $this->addressFactory->createNullAddress();
            }
            return $this->addressFactory->createFromArray(
                (array) Arr::get($response, 'd', []),
                $client
            );
        };
        return $this->loadCached($cacheKey, $resolver, Address::getCacheTtl(), $this->cache);
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
            fn(array $address) => $this->addressFactory->createFromArray($address, $client),
            Arr::get($response, 'd.results', [])
        );
    }
}
