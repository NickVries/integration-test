<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Authentication\Domain\AuthServerInterface;
use App\Shipments\Domain\Address\AddressesGateway;
use Carbon\Carbon;
use Closure;
use GuzzleHttp\Client;
use Psr\SimpleCache\CacheInterface;

class CacheAddresses extends AbstractCachingCommand
{
    protected $signature = 'exact:cache:addresses';

    protected $description = <<<'DESC'
        Will access all addresses created in the last 6 hours and proactively cache them.
        This command will also automatically cache related contacts too.
    DESC;

    public function __construct(
        private AddressesGateway $addressesGateway,
        AuthServerInterface $authServer,
        CacheInterface $cache
    ) {
        parent::__construct($authServer, $cache);
    }

    protected function getEntityName(): string
    {
        return 'Address';
    }

    protected function cacheableItemsResolver(): Closure
    {
        $start = Carbon::now()->subHours(6);
        $end = Carbon::now();

        return fn(Client $client) => $this->addressesGateway->fetchByDateRange($start, $end, $client);
    }
}
