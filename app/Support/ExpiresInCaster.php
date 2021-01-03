<?php

declare(strict_types=1);

namespace App\Support;

use App\Authentication\ExpiresIn;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;

class ExpiresInCaster implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ExpiresIn
    {
        return new ExpiresIn((int) $value);
    }

    /**
     * @param Model     $model
     * @param string    $key
     * @param ExpiresIn $value
     * @param array     $attributes
     * @return mixed
     */
    #[Pure]
    public function set($model, string $key, $value, array $attributes): int
    {
        return $value->toSeconds();
    }
}
