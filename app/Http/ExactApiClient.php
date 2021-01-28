<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use function config;

class ExactApiClient extends GuzzleClient
{
    private static ?HandlerStack $handler = null;

    public function __construct(string $accessToken)
    {
        parent::__construct(array_filter([
            'headers'  => [
                'Authorization' => "Bearer ${accessToken}",
                'Accept'        => 'application/json',
                'Content-type'  => 'application/json',
            ],
            'base_uri' => config('exact.api.base_uri') . config('exact.api.version'),
            'handler'  => self::$handler,
        ]));
    }

    /**
     * Set a guzzle stack handler
     *
     * Useful for testing @see MockHandler
     *
     * @param HandlerStack $handler
     */
    public static function setHandler(HandlerStack $handler): void
    {
        self::$handler = $handler;
    }
}
