<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Authentication\Domain\AuthServerInterface;
use App\Shipments\Domain\Item\ItemsGateway;
use Carbon\Carbon;
use Closure;
use GuzzleHttp\Client;
use Psr\SimpleCache\CacheInterface;

class CacheItems extends AbstractCachingCommand
{
    protected $signature = 'exact:cache:items';

    protected $description = <<<'DESC'
        Will access all items created in the last 6 hours and proactively cache them.
    DESC;

    public function __construct(
        private ItemsGateway $itemsGateway,
        AuthServerInterface $authServer,
        CacheInterface $cache
    ) {
        parent::__construct($authServer, $cache);
    }

    protected function getEntityName(): string
    {
        return 'Item';
    }

    protected function cacheableItemsResolver(): Closure
    {
        $start = Carbon::now()->subHours(6);
        $end = Carbon::now();

        return fn(Client $client) => $this->itemsGateway->fetchByDateRange($start, $end, $client);
    }
}
