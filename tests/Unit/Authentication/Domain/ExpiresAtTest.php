<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\Domain;

use App\Authentication\Domain\ExpiresAt;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ExpiresAtTest extends TestCase
{
    public function test_should_create_expires_at_value_object_based_on_a_date_time_object(): void
    {
        $this->expectNotToPerformAssertions();

        new ExpiresAt(Carbon::now());
    }

    public function test_should_validate_if_expired(): void
    {
        $expiresAt = new ExpiresAt(Carbon::now()->addHour());

        Carbon::setTestNow(Carbon::now()->addYear());
        self::assertTrue($expiresAt->hasExpired());
        Carbon::setTestNow();
        self::assertFalse($expiresAt->hasExpired());
    }
}
