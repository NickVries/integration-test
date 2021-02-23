<?php

declare(strict_types=1);

namespace App\Http;

use Closure;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Arr;
use ODataQuery\ODataResourcePath;
use ODataQuery\Select\ODataQuerySelect;
use Psr\Http\Message\ResponseInterface;
use function config;
use function json_decode;

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
            'base_uri' => config('exact.api.base_uri') . config('exact.api.version') . '/',
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

    /**
     * Get current division
     *
     * @return int
     */
    public function getDivision(): int
    {
        $path = new ODataResourcePath('current/Me');
        $path->setSelect(new ODataQuerySelect(['CurrentDivision']));

        return $this
            ->getAsync((string) $path)
            ->then($this->jsonDecode())
            ->then(function (array $response) {
                return (int) Arr::get($response, 'd.results.0.CurrentDivision');
            })
            ->wait();
    }

    private function jsonDecode(): Closure
    {
        return static function (ResponseInterface $response) {
            return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        };
    }
}
