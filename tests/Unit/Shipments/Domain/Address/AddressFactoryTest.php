<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Address;

use App\Shipments\Domain\Account\Account;
use App\Shipments\Domain\Account\AccountsGateway;
use App\Shipments\Domain\Address\AddressFactory;
use Faker\Factory;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\TestCase;
use function random_int;
use function strtoupper;

class AddressFactoryTest extends TestCase
{
    public function test_should_create_address_from_array(): void
    {
        $faker = Factory::create();

        $email = $faker->email;

        $factory = new AddressFactory(
            Mockery::mock(AccountsGateway::class, [
                'fetchOneByAccountId' => Mockery::mock(Account::class, [
                    'getEmail' => $email,
                ]),
            ])
        );

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $countryCode = $faker->countryCode;
        $postcode = $faker->postcode;
        $stateCode = strtoupper(Str::random(2));
        $streetNumber = random_int(10, 99);
        $streetName = $faker->streetName;
        $address2 = $faker->streetAddress;
        $address3 = $faker->streetAddress;
        $city = $faker->city;
        $company = $faker->company;
        $phoneNumber = $faker->phoneNumber;

        $address = $factory->createFromArray([
            'Account'      => $faker->uuid,
            'ContactName'  => $firstName . ' ' . $lastName,
            'Country'      => $countryCode,
            'Postcode'     => $postcode,
            'State'        => $stateCode,
            'AddressLine1' => $streetName . ' ' . $streetNumber . 'A',
            'AddressLine2' => $address2,
            'AddressLine3' => $address3,
            'City'         => $city,
            'AccountName'  => $company,
            'Phone'        => $phoneNumber,
        ], Mockery::mock(Client::class));

        self::assertEquals([
            'street_1'             => $streetName,
            'street_2'             => $address2 . ' ' . $address3,
            'street_number'        => $streetNumber,
            'street_number_suffix' => 'A',
            'postal_code'          => $postcode,
            'city'                 => $city,
            'country_code'         => $countryCode,
            'first_name'           => $firstName,
            'last_name'            => $lastName,
            'company'              => $company,
            'phone_number'         => $phoneNumber,
            'email'                => $email,
        ], $address->toShipmentAddress()->toArray());
    }

    public function test_should_create_address_from_array_with_nulls(): void
    {
        $factory = new AddressFactory(
            Mockery::mock(AccountsGateway::class)
        );

        $address = $factory->createFromArray([
            'Account'      => null,
            'ContactName'  => null,
            'Country'      => null,
            'Postcode'     => null,
            'State'        => null,
            'AddressLine1' => null,
            'AddressLine2' => null,
            'AddressLine3' => null,
            'City'         => null,
            'AccountName'  => null,
            'Phone'        => null,
        ], Mockery::mock(Client::class));

        self::assertEquals([], $address->toShipmentAddress()->toArray());
    }

    public function test_should_use_account_name_when_no_contact_name_is_available(): void
    {
        $faker = Factory::create();

        $email = $faker->email;

        $factory = new AddressFactory(
            Mockery::mock(AccountsGateway::class, [
                'fetchOneByAccountId' => Mockery::mock(Account::class, [
                    'getEmail' => $email,
                ]),
            ])
        );

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $countryCode = $faker->countryCode;
        $postcode = $faker->postcode;
        $stateCode = strtoupper(Str::random(2));
        $streetNumber = random_int(10, 99);
        $streetName = $faker->streetName;
        $address2 = $faker->streetAddress;
        $address3 = $faker->streetAddress;
        $city = $faker->city;
        $phoneNumber = $faker->phoneNumber;

        $address = $factory->createFromArray([
            'Account'      => $faker->uuid,
            'Country'      => $countryCode,
            'Postcode'     => $postcode,
            'State'        => $stateCode,
            'AddressLine1' => $streetName . ' ' . $streetNumber . 'A',
            'AddressLine2' => $address2,
            'AddressLine3' => $address3,
            'City'         => $city,
            'AccountName'  => $firstName . ' ' . $lastName,
            'Phone'        => $phoneNumber,
        ], Mockery::mock(Client::class));

        self::assertEquals([
            'street_1'             => $streetName,
            'street_2'             => $address2 . ' ' . $address3,
            'street_number'        => $streetNumber,
            'street_number_suffix' => 'A',
            'postal_code'          => $postcode,
            'city'                 => $city,
            'country_code'         => $countryCode,
            'first_name'           => $firstName,
            'last_name'            => $lastName,
            'phone_number'         => $phoneNumber,
            'email'                => $email,
        ], $address->toShipmentAddress()->toArray());
    }
}
