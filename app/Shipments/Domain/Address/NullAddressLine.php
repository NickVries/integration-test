<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

class NullAddressLine extends AddressLine
{
    /** @noinspection MagicMethodsValidityInspection */
    public function __construct()
    {
    }

    public function getStreet(): string
    {
        return '';
    }

    public function getHouseNumber(): string
    {
        return '';
    }

    public function getHouseNumberExt(): string
    {
        return '';
    }

    public function __toString(): string
    {
        return '';
    }
}
