<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

use App\Authentication\Domain\Exceptions\InvalidExpiresInSecondsException;
use Carbon\Carbon;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
class ExpiresIn
{
    public function __construct(private int $seconds)
    {
        if ($this->seconds < 0) {
            throw new InvalidExpiresInSecondsException(
                "expires_in seconds cannot be a negative value, {$this->seconds} given"
            );
        }
    }

    public function toExpiresAt(): ExpiresAt
    {
        return new ExpiresAt(Carbon::now()->addSeconds($this->seconds));
    }

    public function toSeconds(): int
    {
        return $this->seconds;
    }
}
