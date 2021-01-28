<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Address;

use App\Shipments\Domain\Address\AddressLine;
use PHPUnit\Framework\TestCase;

class AddressLineTest extends TestCase
{
    public function test_should_create_address_line_and_extract_house_number_and_extension(): void
    {
        $addressLine = new AddressLine('Anton Constadsestraat 18');

        self::assertEquals('Anton Constadsestraat', $addressLine->getStreet());
        self::assertEquals('18', $addressLine->getHouseNumber());
        self::assertEmpty($addressLine->getHouseNumberExt());

        $addressLine = new AddressLine('Javastraat 40 4-A');

        self::assertEquals('Javastraat', $addressLine->getStreet());
        self::assertEquals('40', $addressLine->getHouseNumber());
        self::assertEquals('4-A', $addressLine->getHouseNumberExt());
    }
}
