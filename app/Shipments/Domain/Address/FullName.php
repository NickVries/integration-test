<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use Illuminate\Support\Arr;
use JetBrains\PhpStorm\Pure;
use function preg_match;

class FullName
{
    private const NAMES_PATTERN = '/^(?P<first_name>.+?)\s(?P<last_name>.+?)$/';

    private string $name;

    #[Pure]
    public function __construct(string $name)
    {
        $this->name = trim($name);
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
        preg_match(self::NAMES_PATTERN, $this->name, $matches);
        return trim((string) Arr::get($matches, $key, $this->name));
    }

    public function isEmpty(): bool
    {
        return empty($this->name);
    }
}
