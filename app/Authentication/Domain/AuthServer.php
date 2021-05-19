<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

use App\Authentication\Domain\Exceptions\AuthRequestException;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;

class AuthServer implements AuthServerInterface
{
    public function __construct(
        // TODO Use these client credentials with the remote authorization server
        private string $clientId,
        private string $clientSecret
    ) {
    }

    /**
     * @inheritDoc
     * @throws AuthRequestException|GuzzleException
     */
    #[ArrayShape(AuthServerInterface::RESPONSE_FORMAT)]
    public function requestAccessToken(
        string $code,
        string $redirectUri
    ): array {
        // TODO Implement logic to request an access token from the authorization server
        // TODO Tip: use the formatResponse() method below to ensure correct data types for that expires_in and token_type
    }

    /**
     * @inheritDoc
     * @throws AuthRequestException|GuzzleException
     */
    #[ArrayShape(AuthServerInterface::RESPONSE_FORMAT)]
    public function refreshToken(
        string $refreshToken
    ): array {
        // TODO Implement logic to refresh access token with the authorization server
        // TODO Tip: use the formatResponse() method below to ensure correct data types for that expires_in and token_type
    }

    /**
     * @param array $response
     * @return array
     */
    private function formatResponse(array $response): array
    {
        if (isset($response['expires_in'])) {
            $response['expires_in'] = new ExpiresIn((int) $response['expires_in']);
        }

        if (isset($response['token_type'])) {
            $response['token_type'] = new TokenType($response['token_type']);
        }

        return $response;
    }
}
