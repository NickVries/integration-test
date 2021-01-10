<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use Illuminate\Support\Arr;
use function preg_match;

class ContactName
{
    private const NAMES_PATTERN = '/^(?P<first_name>.+?)\s(?P<last_name>.+?)$/';

    public function __construct(private string $contactName)
    {
    }

    public function getFirstName(): string
    {
        return $this->extract('first_name');
    }

    public function getLastName(): string
    {
        return $this->extract('last_name');
    }

    private function extract(string $key): string
    {
        preg_match(self::NAMES_PATTERN, $this->contactName, $matches);
        return (string) Arr::get($matches, $key, $this->contactName);
    }
}
