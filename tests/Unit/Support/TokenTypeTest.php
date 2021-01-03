<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Authentication\TokenType;
use App\Support\TokenTypeCaster;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use PHPUnit\Framework\TestCase;

class TokenTypeTest extends TestCase
{
    public function test_should_transform_expires_in_int_to_value_object(): void
    {
        $caster = new TokenTypeCaster();

        $tokenType = $caster->get(
            Mockery::mock(Model::class),
            '',
            'bearer',
            []
        );

        self::assertEquals(TokenType::BEARER(), $tokenType);
    }

    public function test_should_transform_expires_in_value_object_to_int(): void
    {
        $caster = new TokenTypeCaster();

        $tokenTypeMock = Mockery::mock(TokenType::class, ['getValue' => 'bearer']);
        $modelMock = Mockery::mock(Model::class);

        self::assertEquals('bearer', $caster->set($modelMock, '', $tokenTypeMock, []));
    }
}
