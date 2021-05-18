<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

use App\Authentication\Domain\Exceptions\AuthSessionExpiredException;
use DateInterval;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use MyParcelCom\Integration\ShopId;
use Ramsey\Uuid\Uuid;

class AuthorizationSession
{
    private const TOKEN_LENGTH = 20;
    private const TOKEN_PREFIX = 'auth_session:';
    private const TOKEN_TTL = 'PT5M'; // 5 minutes

    public function __construct(
        private Repository $cache
    ) {
    }

    public function save(ShopId $shopId, string $redirectUri): string
    {
        $token = Str::random(self::TOKEN_LENGTH);

        $payload = [
            'shop_id'      => $shopId->toString(),
            'redirect_uri' => $redirectUri,
        ];

        $this->cache->put(
            self::TOKEN_PREFIX . $token,
            $payload,
            new DateInterval(self::TOKEN_TTL)
        );

        return $token;
    }

    #[ArrayShape([
        'shop_id'      => ShopId::class,
        'redirect_uri' => 'string',
    ])]
    public function fetch(
        string $token
    ): array {
        $key = self::TOKEN_PREFIX . $token;

        if (!$this->cache->has($key)) {
            throw new AuthSessionExpiredException();
        }

        $payload = $this->cache->pull($key);

        return [
            'shop_id'      => new ShopId(Uuid::fromString($payload['shop_id'])),
            'redirect_uri' => $payload['redirect_uri'],
        ];
    }
}
