<?php

declare(strict_types=1);

namespace App\Support;

use App\Authentication\Domain\TokenType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;

class TokenTypeCaster implements CastsAttributes
{
    #[Pure]
    public function get(
        $model, string $key, $value, array $attributes
    ): TokenType {
        return new TokenType((string) $value);
    }

    /**
     * @param Model     $model
     * @param string    $key
     * @param TokenType $value
     * @param array     $attributes
     * @return mixed
     */
    #[Pure]
    public function set($model, string $key, $value, array $attributes): string
    {
        return (string) $value->getValue();
    }
}
