<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Item;

use Crisu83\Conversion\Quantity\Mass\Mass;
use Crisu83\Conversion\Quantity\Mass\Unit;

class Weight
{
    public function __construct(private int $grams)
    {
    }

    public static function createFromUnit(?float $weight, ?string $unit): self
    {
        $unit = empty($unit) ? Unit::KILOGRAM : $unit;

        if ($weight === null) {
            return new NullWeight();
        }

        return match ($unit) {
            Unit::GRAM => new self((int) $weight),
            Unit::KILOGRAM => new self(self::convertTo($weight, Unit::KILOGRAM)),
            Unit::OUNCE => new self(self::convertTo($weight, Unit::OUNCE)),
            Unit::POUND => new self(self::convertTo($weight, Unit::POUND)),
        };
    }

    private static function convertTo(float $weight, string $convertFrom): int
    {
        return (int) (new Mass($weight, $convertFrom))->to(Unit::GRAM)->getValue();
    }

    public function toGrams(): int
    {
        return $this->grams;
    }
}
