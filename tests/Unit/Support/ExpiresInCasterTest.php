<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Authentication\Domain\ExpiresIn;
use App\Support\ExpiresInCaster;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use PHPUnit\Framework\TestCase;
use function random_int;

class ExpiresInCasterTest extends TestCase
{
    public function test_should_transform_expires_in_int_to_value_object(): void
    {
        $caster = new ExpiresInCaster();

        /** @noinspection PhpUnhandledExceptionInspection */
        $seconds = random_int(1, 600);

        $expiresIn = $caster->get(
            Mockery::mock(Model::class),
            '',
            $seconds,
            []
        );

        self::assertEquals(new ExpiresIn($seconds), $expiresIn);
    }

    public function test_should_transform_expires_in_value_object_to_int(): void
    {
        $caster = new ExpiresInCaster();

        /** @noinspection PhpUnhandledExceptionInspection */
        $seconds = random_int(1, 600);

        $expiresInMock = Mockery::mock(ExpiresIn::class, ['toSeconds' => $seconds]);
        $modelMock = Mockery::mock(Model::class);

        self::assertEquals($seconds, $caster->set($modelMock, '', $expiresInMock, []));
    }
}
