<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use JetBrains\PhpStorm\ArrayShape;
use VIISON\AddressSplitter\AddressSplitter;

class AddressLine
{
    #[ArrayShape([
        'streetName'       => 'string',
        'houseNumberParts' => 'array',
    ])]
    private array $split;

    public function __construct(private string $addressLine)
    {
        $this->split = AddressSplitter::splitAddress($this->addressLine);
    }

    public function getStreet(): string
    {
        return $this->split['streetName'];
    }

    public function getHouseNumber(): string
    {
        return $this->split['houseNumberParts']['base'];
    }

    public function getHouseNumberExt(): string
    {
        return $this->split['houseNumberParts']['extension'];
    }

    public function __toString(): string
    {
        return $this->addressLine;
    }
}
