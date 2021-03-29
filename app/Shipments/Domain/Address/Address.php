<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use App\Shipments\Domain\Cacheable;
use DateInterval;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use MyParcelCom\Integration\Shipment\Address as ShipmentAddress;
use Ramsey\Uuid\UuidInterface;
use function trim;

#[Immutable]
class Address implements Cacheable
{
    public function __construct(
        private UuidInterface $id,
        private FullName $contactName,
        private ?string $country,
        private ?string $postcode,
        private ?string $state,
        private AddressLine $addressLine1,
        private AddressLine $addressLine2,
        private AddressLine $addressLine3,
        private ?string $city,
        private ?string $accountName,
        private ?string $phone,
        private ?string $email,
    ) {
    }

    public function toShipmentAddress(): ShipmentAddress
    {
        $company = trim((string) $this->accountName);
        $fullName = $this->contactName;

        // in case contact name is unavailable but there is
        // account name then the account name holds the person's full name
        // and there is no company set
        if (!empty($company) && $fullName->isEmpty()) {
            $fullName = new FullName($company);
            $company = null;
        }

        return new ShipmentAddress(
            street1: $this->addressLine1->getStreet(),
            street2: trim($this->addressLine2 . ' ' . $this->addressLine3),
            streetNumber: (int) $this->addressLine1->getHouseNumber(),
            streetNumberSuffix: $this->addressLine1->getHouseNumberExt(),
            postalCode: trim((string) $this->postcode),
            city: trim((string) $this->city),
            countryCode: trim((string) $this->country),
            firstName: $fullName->getFirstName(),
            lastName: $fullName->getLastName(),
            company: $company,
            email: trim($this->email),
            phoneNumber: trim((string) $this->phone),
        );
    }

    #[Pure]
    public function getCacheKey(): string
    {
        return self::generateCacheKey($this->id->toString());
    }

    #[Pure]
    public static function generateCacheKey(string $identifier): string
    {
        return "address_${identifier}";
    }

    public function __toString(): string
    {
        return "Address [{$this->id}]";
    }

    public static function getCacheTtl(): DateInterval
    {
        return new DateInterval('P1M');
    }
}
