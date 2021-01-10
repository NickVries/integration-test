<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

use Carbon\Carbon;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
class ExpiresAt
{
    public function __construct(
        private Carbon $dateTime
    ) {
    }

    public function hasExpired(): bool
    {
        return $this->dateTime <= Carbon::now();
    }

    public function toDateTimeString(): string
    {
        return $this->dateTime->toDateTimeString();
    }
}
