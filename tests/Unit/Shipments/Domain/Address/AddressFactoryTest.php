<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Address;

use App\Shipments\Domain\Address\AddressFactory;
use Faker\Factory;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use function random_int;
use function strtoupper;

class AddressFactoryTest extends TestCase
{
    public function test_should_create_address_from_array(): void
    {
        $faker = Factory::create();

        $factory = new AddressFactory();

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
        ]);

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
        ], $address->toJsonApiArray());
    }

    public function test_should_create_address_from_array_with_nulls(): void
    {
        $factory = new AddressFactory();

        $address = $factory->createFromArray([
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
        ]);

        self::assertEquals([], $address->toJsonApiArray());
    }
}
