<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

use MyCLabs\Enum\Enum;

/**
 * @method static TokenType BEARER()
 */
class TokenType extends Enum
{
    private const BEARER = 'bearer';
}
