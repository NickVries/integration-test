<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Support\ExpiresAtCaster;
use App\Support\ExpiresInCaster;
use App\Support\ShopIdCaster;
use App\Support\TokenTypeCaster;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

/**
 * @property ShopId    shop_id
 * @property string    access_token
 * @property string    refresh_token
 * @property ExpiresIn expires_in
 * @property ExpiresAt expires_at
 * @property string    token_type
 */
class Token extends Model
{
    use HasTimestamps;

    protected $casts = [
        'shop_id'    => ShopIdCaster::class,
        'expires_in' => ExpiresInCaster::class,
        'expires_at' => ExpiresAtCaster::class,
        'token_type' => TokenTypeCaster::class,
    ];

    public static function create(
        ShopId $shopId,
        string $accessToken,
        string $refreshToken,
        ExpiresIn $expiresIn,
        TokenType $tokenType
    ): self {
        $token = new self();
        $token->shop_id = $shopId;
        $token->access_token = $accessToken;
        $token->refresh_token = $refreshToken;
        $token->expires_in = $expiresIn;
        $token->expires_at = $token->expires_in->toExpiresAt();
        $token->token_type = $tokenType;

        return $token;
    }

    /**
     * This function will request new access_token
     * using the existing saved refresh_token and overwrite the old tokens
     *
     * @param AuthServerInterface $exactOnlineAuthServer
     */
    public function renew(AuthServerInterface $exactOnlineAuthServer): void
    {
        $tokens = $exactOnlineAuthServer->refreshToken($this->refresh_token);

        $this->refresh_token = $tokens['refresh_token'];
        $this->access_token = $tokens['access_token'];
        $this->expires_in = $tokens['expires_in'];
        $this->expires_at = $this->expires_in->toExpiresAt();
        $this->token_type = $tokens['token_type'];
    }

    /**
     * Returns a working access token
     *
     * Difference with only getting the access_token property directly is
     * that in case the existing access token has expired a new one will be requested using the
     * Exact Online auth server
     *
     * @param AuthServerInterface $exactOnlineAuthServer
     * @return string
     */
    public function obtainAccessToken(AuthServerInterface $exactOnlineAuthServer): string
    {
        if ($this->expires_at->hasExpired()) {
            $this->renew($exactOnlineAuthServer);
        }

        return $this->access_token;
    }
}
