<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use Ramsey\Uuid\Uuid;

class NullAddress extends Address
{
    public function __construct()
    {
        parent::__construct(
            Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            new NullFullName(),
            '',
            '',
            '',
            new NullAddressLine(),
            new NullAddressLine(),
            new NullAddressLine(),
            '',
            '',
            '',
            '',
        );
    }
}
