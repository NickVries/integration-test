<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use JetBrains\PhpStorm\Immutable;
use function array_filter;

#[Immutable]
class Address
{
    public function __construct(
        private ContactName $contactName,
        private ?string $country,
        private ?string $postcode,
        private ?string $state,
        private AddressLine $addressLine1,
        private AddressLine $addressLine2,
        private AddressLine $addressLine3,
        private ?string $city,
        private ?string $accountName,
        private ?string $phone,
    ) {
    }

    public function toJsonApiArray(): array
    {
        return array_filter([
            'street_1'             => $this->addressLine1->getStreet(),
            'street_2'             => trim($this->addressLine2 . ' ' . $this->addressLine3),
            'street_number'        => (int) $this->addressLine1->getHouseNumber(),
            'street_number_suffix' => $this->addressLine1->getHouseNumberExt(),
            'postal_code'          => trim((string) $this->postcode),
            'city'                 => trim((string) $this->city),
            'country_code'         => trim((string) $this->country),
            'first_name'           => trim($this->contactName->getFirstName()),
            'last_name'            => trim($this->contactName->getLastName()),
            'company'              => trim((string) $this->accountName),
            'phone_number'         => trim((string) $this->phone),
        ]);
    }
}
