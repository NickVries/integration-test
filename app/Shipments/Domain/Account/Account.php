<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Account;

class Account
{
    public function __construct(
        private string $email
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
