<?php

declare(strict_types=1);

namespace App\Support;

use App\Authentication\Domain\ShopId;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;

class ShopIdCaster implements CastsAttributes
{
    /**
     * @param Model  $model
     * @param string $key
     * @param string $value
     * @param array  $attributes
     * @return ShopId
     */
    public function get($model, string $key, $value, array $attributes): ShopId
    {
        return new ShopId(Uuid::fromString($value));
    }

    #[ArrayShape(['shop_id' => 'string'])]
    public function set(
        $model,
        string $key,
        $value,
        array $attributes
    ): string {
        return $value->toString();
    }
}
