<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\Domain;

use App\Authentication\Domain\Exceptions\InvalidExpiresInSecondsException;
use App\Authentication\Domain\ExpiresAt;
use App\Authentication\Domain\ExpiresIn;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use function random_int;

class ExpiresInTest extends TestCase
{
    public function test_should_throw_exception_if_expires_in_is_lower_than_zero(): void
    {
        $this->expectException(InvalidExpiresInSecondsException::class);

        new ExpiresIn(-1);
    }

    public function test_should_create_expires_in_value_object_with_valid_seconds(): void
    {
        $this->expectNotToPerformAssertions();

        /** @noinspection PhpUnhandledExceptionInspection */
        new ExpiresIn(random_int(0, 600));
    }

    public function test_should_create_expires_at_date_time_from_expires_in_value_object(): void
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);
        /** @noinspection PhpUnhandledExceptionInspection */
        $seconds = random_int(0, 600);
        $expectedExpiresAt = new ExpiresAt($now->copy()->addSeconds($seconds));

        $expiresIn = new ExpiresIn($seconds);

        self::assertEquals($expectedExpiresAt, $expiresIn->toExpiresAt());

        Carbon::setTestNow();
    }

    public function test_should_convert_expires_in_value_object_to_seconds(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $seconds = random_int(0, 600);
        $expiresIn = new ExpiresIn($seconds);

        self::assertEquals($seconds, $expiresIn->toSeconds());
    }
}
