<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Item;

use Ramsey\Uuid\Uuid;

class NullItem extends Item
{
    public function __construct()
    {
        parent::__construct(
            Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            '',
            new NullWeight(),
            ''
        );
    }
}
