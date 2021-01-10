<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Address;

use App\Shipments\Domain\Address\ContactName;
use PHPUnit\Framework\TestCase;

class ContactNameTest extends TestCase
{
    public function test_should_create_contact_name_and_get_first_and_last_names(): void
    {
        $contactName = new ContactName('First Last name');

        self::assertEquals('First', $contactName->getFirstName());
        self::assertEquals('Last name', $contactName->getLastName());

        $contactName = new ContactName('John Doe');

        self::assertEquals('John', $contactName->getFirstName());
        self::assertEquals('Doe', $contactName->getLastName());

        $contactName = new ContactName('John');

        self::assertEquals('John', $contactName->getFirstName());
        self::assertEquals('John', $contactName->getLastName());
    }
}
