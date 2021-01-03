<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Authentication\Exceptions\AuthRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Utils;
use JetBrains\PhpStorm\ArrayShape;

class AuthServer implements AuthServerInterface
{
    private const TOKEN_ENDPOINT = 'oauth2/token';

    public function __construct(
        private Client $client,
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri
    ) {
    }

    /**
     * @inheritDoc
     * @throws AuthRequestException|GuzzleException
     */
    #[ArrayShape(AuthServerInterface::RESPONSE_FORMAT)]
    public function requestAccessToken(
        string $code
    ): array {
        return $this->request([
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->redirectUri,
            'code'          => $code,
        ]);
    }

    /**
     * @inheritDoc
     * @throws AuthRequestException|GuzzleException
     */
    #[ArrayShape(AuthServerInterface::RESPONSE_FORMAT)]
    public function refreshToken(
        string $refreshToken
    ): array {
        return $this->request([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * @param array $body
     * @return array
     * @throws AuthRequestException|GuzzleException
     */
    private function request(array $body): array
    {
        try {
            return $this->formatResponse(
                Utils::jsonDecode((string) $this->client->post(self::TOKEN_ENDPOINT, [
                    'body' => $body + [
                            'client_id'     => $this->clientId,
                            'client_secret' => $this->clientSecret,
                        ],
                ])->getBody(), true)
            );
        } catch (RequestException $exception) {
            throw AuthRequestException::fromRequestException($exception);
        }
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
