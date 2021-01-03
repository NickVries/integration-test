<?php

declare(strict_types=1);

namespace App\Authentication;

use JetBrains\PhpStorm\ArrayShape;

interface AuthServerInterface
{
    #[ArrayShape([
        'refresh_token' => 'string',
        'access_token'  => 'string',
        'expires_in'    => ExpiresIn::class,
        'token_type'    => TokenType::class,
    ])]
    public function refreshToken(
        string $refreshToken
    ): array;
}
