<?php

declare(strict_types=1);

namespace App\Support;

use App\Authentication\Domain\TokenType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;
use function is_null;

class TokenTypeCaster implements CastsAttributes
{
    #[Pure]
    public function get(
        $model, string $key, $value, array $attributes
    ): ?TokenType {
        return is_null($value) ? null : new TokenType((string) $value);
    }

    /**
     * @param Model     $model
     * @param string    $key
     * @param TokenType $value
     * @param array     $attributes
     * @return string|null
     */
    #[Pure]
    public function set($model, string $key, $value, array $attributes): ?string
    {
        return is_null($value) ? null : (string) $value->getValue();
    }
}
