<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Authentication\ExpiresAt;
use App\Support\ExpiresAtCaster;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use PHPUnit\Framework\TestCase;

class ExpiresAtCasterTest extends TestCase
{
    public function test_should_transform_expires_in_int_to_value_object(): void
    {
        $caster = new ExpiresAtCaster();

        $dateTime = Factory::create()->dateTime;

        $expiresIn = $caster->get(
            Mockery::mock(Model::class),
            '',
            $dateTime,
            []
        );

        self::assertEquals(new ExpiresAt(Carbon::instance($dateTime)), $expiresIn);
    }

    public function test_should_transform_expires_in_value_object_to_int(): void
    {
        $caster = new ExpiresAtCaster();

        $dateTimeString = Factory::create()->dateTime->format('Y-m-d H:i:s');

        $expiresAtMock = Mockery::mock(ExpiresAt::class, ['toDateTimeString' => $dateTimeString]);
        $modelMock = Mockery::mock(Model::class);

        self::assertEquals($dateTimeString, $caster->set($modelMock, '', $expiresAtMock, []));
    }
}
