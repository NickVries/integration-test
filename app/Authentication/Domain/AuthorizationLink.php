<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use function http_build_query;

class AuthorizationLink
{
    public function __construct(
        private string $clientId,
        private string $redirectUri,
        private string $token
    ) {
    }

    public function buildUri(): UriInterface
    {
        return Uri::fromParts([
            'scheme' => 'https',
            'host'   => 'start.exactonline.nl',
            'path'   => '/api/oauth2/auth',
            'query'  => http_build_query([
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri . '?token=' . $this->token,
                'response_type' => 'code',
            ]),
        ]);
    }
}
