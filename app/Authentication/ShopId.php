<?php

declare(strict_types=1);

namespace App\Authentication;

use JetBrains\PhpStorm\Immutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Immutable]
class ShopId
{
    public function __construct(
        private UuidInterface $uuid
    ) {
    }

    public static function create(): self
    {
        return new self(Uuid::uuid4());
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }
}
