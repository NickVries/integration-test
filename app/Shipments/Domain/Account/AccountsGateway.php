<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Account;

use App\Http\ExactApiDivisionClient;
use App\Shipments\Domain\LoadAndCache;
use App\Shipments\Domain\MakeRequest;
use DateInterval;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use ODataQuery\ODataResourcePath;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\UuidInterface;

class AccountsGateway
{
    use MakeRequest;
    use LoadAndCache;

    private const ENTITY = 'crm/Accounts';

    public function __construct(
        private ExactApiDivisionClient $client,
        private AccountFactory $accountFactory,
        private CacheInterface $cache
    ) {
    }

    public function fetchOneByAccountId(UuidInterface $id): Account
    {
        $cacheKey = "account_${id}";
        $resolver = function () use ($id): Account {
            try {
                $response = $this->request(new ODataResourcePath(self::ENTITY . "(guid'${id}')"));
            } catch (GuzzleException $e) {
                return $this->accountFactory->createNullAccount();
            }
            return $this->accountFactory->createFromArray(
                (array) Arr::get($response, 'd', [])
            );
        };
        $ttl = new DateInterval('P1D');

        return $this->loadCached($cacheKey, $resolver, $ttl, $this->cache);
    }
}
