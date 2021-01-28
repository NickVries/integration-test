<?php

declare(strict_types=1);

namespace App\Support;

use App\Authentication\Domain\ExpiresAt;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;

class ExpiresAtCaster implements CastsAttributes
{
    #[Pure]
    public function get(
        $model, string $key, $value, array $attributes
    ): ExpiresAt {
        return new ExpiresAt(new Carbon($value));
    }

    /**
     * @param Model     $model
     * @param string    $key
     * @param ExpiresAt $value
     * @param array     $attributes
     * @return mixed
     */
    public function set(
        $model, string $key, $value, array $attributes
    ): string {
        return $value->toDateTimeString();
    }
}
