<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use JetBrains\PhpStorm\ArrayShape;

class AddressFactory
{
    public function createFromArray(
        #[ArrayShape([
            'ContactName'  => 'string',
            'Country'      => 'string',
            'Postcode'     => 'string',
            'State'        => 'string',
            'AddressLine1' => 'string',
            'AddressLine2' => 'string',
            'AddressLine3' => 'string',
            'City'         => 'string',
            'AccountName'  => 'string',
            'Phone'        => 'string',
        ])]
        array $address
    ): Address {
        return new Address(
            isset($address['ContactName']) ? new FullName($address['ContactName']) : new NullFullName(),
            $address['Country'] ?? null,
            $address['Postcode'] ?? null,
            $address['State'] ?? null,
            $address['AddressLine1'] ? new AddressLine($address['AddressLine1']) : new NullAddressLine(),
            $address['AddressLine2'] ? new AddressLine($address['AddressLine2']) : new NullAddressLine(),
            $address['AddressLine3'] ? new AddressLine($address['AddressLine3']) : new NullAddressLine(),
            $address['City'] ?? null,
            $address['AccountName'] ?? null,
            $address['Phone'] ?? null,
        );
    }

    public function createNullAddress(): Address
    {
        return new NullAddress();
    }
}
