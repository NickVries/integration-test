<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use App\Http\ExactApiDivisionClient;
use App\Shipments\Domain\LoadAndCache;
use App\Shipments\Domain\MakeRequest;
use DateInterval;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use ODataQuery\ODataResourcePath;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\UuidInterface;
use function array_key_exists;

class AddressesGateway
{
    use MakeRequest;
    use LoadAndCache;

    private const ENTITY = 'crm/Addresses';

    public function __construct(
        private ExactApiDivisionClient $client,
        private AddressFactory $addressFactory,
        private CacheInterface $cache
    ) {
    }

    public function fetchOneByAddressId(UuidInterface $id): Address
    {
        $cacheKey = "address_${id}";
        $resolver = function () use ($id): Address {
            try {
                $response = $this->request(new ODataResourcePath(self::ENTITY . "(guid'${id}')"));
            } catch (GuzzleException $e) {
                return $this->addressFactory->createNullAddress();
            }
            return $this->addressFactory->createFromArray(
                (array) Arr::get($response, 'd', [])
            );
        };
        $ttl = new DateInterval('P1D');

        return $this->loadCached($cacheKey, $resolver, $ttl, $this->cache);
    }
}
