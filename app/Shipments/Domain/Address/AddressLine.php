<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use VIISON\AddressSplitter\AddressSplitter;
use VIISON\AddressSplitter\Exceptions\SplittingException;

class AddressLine
{
    private array $split = [
        'streetName'       => '',
        'houseNumberParts' => [
            'base'      => '',
            'extension' => '',
        ],
    ];

    public function __construct(private string $addressLine)
    {
        try {
            $this->split = AddressSplitter::splitAddress($this->addressLine);
        } catch (SplittingException $exception) {
            $this->split['streetName'] = $this->addressLine;
        }
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
