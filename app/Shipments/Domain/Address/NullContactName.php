<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

class NullContactName extends ContactName
{
    public function __construct()
    {
        parent::__construct('');
    }
}
