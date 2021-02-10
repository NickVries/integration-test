<?php

declare(strict_types=1);

namespace App\Support;

use App\Authentication\Domain\ExpiresIn;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;
use function is_null;

class ExpiresInCaster implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?ExpiresIn
    {
        return is_null($value) ? null : new ExpiresIn((int) $value);
    }

    /**
     * @param Model     $model
     * @param string    $key
     * @param ExpiresIn $value
     * @param array     $attributes
     * @return int|null
     */
    #[Pure]
    public function set($model, string $key, $value, array $attributes): ?int
    {
        return is_null($value) ? null : $value->toSeconds();
    }
}
