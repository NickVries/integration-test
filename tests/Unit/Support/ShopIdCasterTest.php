<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Authentication\Domain\ShopId;
use App\Support\ShopIdCaster;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ShopIdCasterTest extends TestCase
{
    public function test_should_transform_shop_id_string_to_value_object(): void
    {
        $caster = new ShopIdCaster();

        $uuid = Factory::create()->uuid;

        $shopId = $caster->get(
            Mockery::mock(Model::class),
            '',
            $uuid,
            []
        );

        self::assertEquals(new ShopId(Uuid::fromString($uuid)), $shopId);
    }

    public function test_should_transform_shop_id_value_object_to_raw_attributes(): void
    {
        $caster = new ShopIdCaster();

        $uuid = Factory::create()->uuid;

        $shopIdMock = Mockery::mock(ShopId::class, ['toString' => $uuid]);
        $modelMock = Mockery::mock(Model::class);

        self::assertEquals($uuid, $caster->set($modelMock, '', $shopIdMock, []));
    }
}
