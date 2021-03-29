<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Account;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class AccountFactory
{
    #[Pure]
    public function createFromArray(
        #[ArrayShape([
            'Email' => 'string'
        ])]
        array $account
    ): Account
    {
        return new Account((string) $account['Email']);
    }

    public function createNullAccount(): NullAccount
    {
        return new NullAccount();
    }
}
