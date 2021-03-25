<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use App\Shipments\Domain\Account\Account;
use App\Shipments\Domain\Account\AccountsGateway;
use App\Shipments\Domain\Account\NullAccount;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;

class AddressFactory
{
    public function __construct(private AccountsGateway $accountGateway)
    {
    }

    public function createFromArray(
        #[ArrayShape([
            'Account'      => 'string',
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
        $account = $this->fetchAccount($address);

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
            $account->getEmail(),
        );
    }

    private function fetchAccount(array $address): Account
    {
        return !empty($address['Account']) ? $this->accountGateway->fetchOneByAccountId(
            Uuid::fromString($address['Account'])
        ) : new NullAccount();
    }

    public function createNullAddress(): Address
    {
        return new NullAddress();
    }
}
