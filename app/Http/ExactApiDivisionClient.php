<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use function config;

class ExactApiDivisionClient extends GuzzleClient
{
    private static ?HandlerStack $handler = null;

    public function __construct(string $accessToken, string $division)
    {
        parent::__construct(array_filter([
            'headers'  => [
                'Authorization' => "Bearer ${accessToken}",
                'Accept'        => 'application/json',
                'Content-type'  => 'application/json',
            ],
            'base_uri' => config('exact.api.base_uri') . config('exact.api.version') . "/${division}/",
            'handler'  => self::$handler,
        ]));
    }

    /**
     * Set a guzzle stack handler
     *
     * Useful for testing @see MockHandler
     * @param HandlerStack|null $handler
     */
    public static function setHandler(?HandlerStack $handler): void
    {
        self::$handler = $handler;
    }
}
