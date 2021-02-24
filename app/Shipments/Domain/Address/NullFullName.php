<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use JetBrains\PhpStorm\Pure;

class NullFullName extends FullName
{
    #[Pure]
    public function __construct()
    {
        parent::__construct('');
    }

    public function isEmpty(): bool
    {
        return true;
    }
}
