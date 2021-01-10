<?php

declare(strict_types=1);

namespace Tests\Unit\Shipments\Domain\Item;

use App\Shipments\Domain\Item\NullWeight;
use App\Shipments\Domain\Item\Weight;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    public function test_should_create_weight_from_kg(): void
    {
        $weight = Weight::createFromUnit(1, 'kg');
        self::assertEquals(1000, $weight->toGrams());
    }

    public function test_should_create_null_weight_when_no_weight_is_provided(): void
    {
        $weight = Weight::createFromUnit(null, null);
        self::assertInstanceOf(NullWeight::class, $weight);
    }
}
