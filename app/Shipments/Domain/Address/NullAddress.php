<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

class NullAddress extends Address
{
    public function __construct()
    {
        parent::__construct(
            new NullContactName(),
            '',
            '',
            '',
            new NullAddressLine(),
            new NullAddressLine(),
            new NullAddressLine(),
            '',
            '',
            '',
        );
    }
}
