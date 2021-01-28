<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\Domain;

use App\Authentication\Domain\ShopId;
use ArgumentCountError;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class ShopIdTest extends TestCase
{
    public function test_should_fail_creating_shop_id_value_object_because_of_no_uuid(): void
    {
        $this->expectException(ArgumentCountError::class);
        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpExpressionResultUnusedInspection */
        new ShopId();
    }

    public function test_should_create_shop_id_value_object_with_uuid(): void
    {
        $this->expectNotToPerformAssertions();
        new ShopId(Mockery::mock(UuidInterface::class));
    }

    public function test_should_create_shop_id_value_object_generating_uuid(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(ShopId::class, ShopId::create());
    }

    public function test_should_convert_shop_id_value_object_to_uuid_string(): void
    {
        self::assertIsString((string) ShopId::create());
        self::assertIsString(ShopId::create()->toString());
    }
}
