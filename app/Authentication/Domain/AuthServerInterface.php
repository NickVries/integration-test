<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

use App\Authentication\Domain\Exceptions\AuthRequestException;
use JetBrains\PhpStorm\ArrayShape;

interface AuthServerInterface
{
    public const RESPONSE_FORMAT = [
        'refresh_token'     => 'string',
        'access_token'      => 'string',
        'expires_in'        => ExpiresIn::class,
        'token_type'        => TokenType::class,
        'error_description' => 'string',
        'error'             => 'string',
    ];

    /**
     * Request the initial access and refresh tokens based on authorization code grant
     *
     * @param string $code
     * @param string $redirectUri
     * @return array
     * @throws AuthRequestException
     */
    #[ArrayShape(self::RESPONSE_FORMAT)]
    public function requestAccessToken(
        string $code,
        string $redirectUri
    ): array;

    /**
     * With provided refresh token get a new set of access + refresh tokens
     *
     * @param string $refreshToken
     * @return array
     */
    #[ArrayShape(self::RESPONSE_FORMAT)]
    public function refreshToken(
        string $refreshToken
    ): array;
}
