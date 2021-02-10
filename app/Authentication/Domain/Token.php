<?php
/** @noinspection TraitsPropertiesConflictsInspection */

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace App\Authentication\Domain;

use App\Support\ExpiresAtCaster;
use App\Support\ExpiresInCaster;
use App\Support\ShopIdCaster;
use App\Support\TokenTypeCaster;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;

/**
 * @property ShopId|null    shop_id
 * @property string|null    access_token
 * @property string|null    refresh_token
 * @property ExpiresIn|null expires_in
 * @property ExpiresAt|null expires_at
 * @property string|null    token_type
 */
class Token extends Model
{
    use HasTimestamps;
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = 'shop_id';

    protected $keyType = 'string';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'token_type',
        'expires_in',
        'expires_at',
    ];

    protected $casts = [
        'shop_id'    => ShopIdCaster::class,
        'expires_in' => ExpiresInCaster::class,
        'expires_at' => ExpiresAtCaster::class,
        'token_type' => TokenTypeCaster::class,
    ];

    #[Pure]
    public static function create(
        ShopId $shopId
    ): self {
        $token = new self();
        $token->shop_id = $shopId;

        return $token;
    }

    /**
     * Find an existing token or create a new one
     *
     * @param ShopId $shopId
     * @return self
     */
    public static function findOrCreate(ShopId $shopId): self
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return self::query()->find($shopId->toString()) ?? self::create($shopId);
    }

    /**
     * Find a token by shop_id
     *
     * @param ShopId $shopId
     * @return self|null
     */
    public static function findByShopId(ShopId $shopId): ?self
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return self::query()->find($shopId->toString());
    }

    /**
     * This function will request new access_token
     * using the existing saved refresh_token and overwrite the old tokens
     *
     * @param AuthServerInterface $exactOnlineAuthServer
     */
    private function renew(AuthServerInterface $exactOnlineAuthServer): void
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
     * @param AuthServerInterface $authServer
     * @return string
     */
    public function obtainAccessToken(AuthServerInterface $authServer): string
    {
        if ($this->expires_at->hasExpired()) {
            $this->renew($authServer);
        }

        return $this->access_token;
    }

    /**
     * Registers an event that ensures that before saving the @link expires_at property is updated if empty
     */
    protected static function booted(): void
    {
        self::saving(function (self $token) {
            if (!$token->expires_at && $token->expires_in) {
                $token->expires_at = $token->expires_in->toExpiresAt();
            }
        });
    }

    /**
     * Make sure that all attributes are empty
     */
    public function nullify(): void
    {
        $this->fill([
            'access_token'  => null,
            'refresh_token' => null,
            'token_type'    => null,
            'expires_in'    => null,
            'expires_at'    => null,
        ]);
    }
}
