<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Item;

use JetBrains\PhpStorm\Pure;

class NullWeight extends Weight
{
    #[Pure]
    public function __construct()
    {
        parent::__construct(0);
    }
}
