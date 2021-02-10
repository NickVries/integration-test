<?php

declare(strict_types=1);

namespace App\Support;

use App\Authentication\Domain\ExpiresAt;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;
use function is_null;

class ExpiresAtCaster implements CastsAttributes
{
    #[Pure]
    public function get(
        $model, string $key, $value, array $attributes
    ): ?ExpiresAt {
        return is_null($value) ? null : new ExpiresAt(new Carbon($value));
    }

    /**
     * @param Model     $model
     * @param string    $key
     * @param ExpiresAt $value
     * @param array     $attributes
     * @return string|null
     */
    public function set(
        $model, string $key, $value, array $attributes
    ): ?string {
        return is_null($value) ? null : $value->toDateTimeString();
    }
}
