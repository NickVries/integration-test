<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Authentication\Domain\AuthServerInterface;
use App\Authentication\Domain\Token;
use App\Http\ExactApiClient;
use App\Shipments\Domain\Cacheable;
use Closure;
use Illuminate\Console\Command;
use Psr\SimpleCache\CacheInterface;
use Throwable;
use function array_walk;

/**
 * This class extracts common logic used by caching commands
 */
abstract class AbstractCachingCommand extends Command
{
    public function __construct(
        private AuthServerInterface $authServer,
        private CacheInterface $cache
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        // we will cache entries for each active tokens
        Token::all()->each($this->proactiveCache($this->getEntityName(), $this->cacheableItemsResolver()));
    }

    /**
     * @param string   $for      Human readable name of the cached entity type
     * @param callable $resolver Closure which when invoked should return an array of {@see Cacheable} objects
     * @return Closure
     */
    private function proactiveCache(string $for, callable $resolver): Closure
    {
        return function (Token $token) use ($for, $resolver) {
            $shopId = $token->shop_id;
            $this->output->title("Processing for shop ${shopId}");

            try {
                $client = ExactApiClient::createWithDivision($this->authServer, $token);
            } catch (Throwable $e) {
                $this->warn("Cannot authenticate shop ${shopId}, skipping...");
                return;
            }

            $this->info("Fetching and caching ${for} entries from the last 6 hours for shop ${shopId}...");

            $entries = $resolver($client);

            array_walk($entries, $this->cache());
        };
    }

    private function cache(): Closure
    {
        return function (Cacheable $cacheable) {
            $this->info("Caching {$cacheable->getCacheKey()}...");
            $this->cache->set($cacheable->getCacheKey(), $cacheable, $cacheable::getCacheTtl());
        };
    }

    /**
     * Human-readable name of the cacheable entity
     *
     * @return string
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns a closure which when invoked should return an array of {@see Cacheable} objects
     *
     * @return Closure
     */
    abstract protected function cacheableItemsResolver(): Closure;
}
