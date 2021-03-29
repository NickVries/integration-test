<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Account;

class NullAccount extends Account
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct()
    {
    }

    public function getEmail(): string
    {
        return '';
    }
}
