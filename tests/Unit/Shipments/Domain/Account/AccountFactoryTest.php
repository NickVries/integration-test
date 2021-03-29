<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Account;

use App\Shipments\Domain\Account\AccountFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class AccountFactoryTest extends TestCase
{
    public function test_should_create_account_from_array(): void
    {
        $faker = Factory::create();

        $factory = new AccountFactory();

        $email = $faker->email;

        $account = $factory->createFromArray([
            'Email' => $email,
        ]);

        self::assertEquals($email, $account->getEmail());
    }

    public function test_should_create_account_from_array_with_nulls(): void
    {
        $factory = new AccountFactory();

        $account = $factory->createFromArray([
            'Email' => null,
        ]);

        self::assertEmpty($account->getEmail());
    }
}
