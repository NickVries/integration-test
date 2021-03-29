<?php

declare(strict_types=1);

namespace App\Http;

use App\Authentication\Domain\AuthServerInterface;
use App\Authentication\Domain\Token;
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

    public function __construct(string $accessToken, ?string $division = null)
    {
        $baseUri = config('exact.api.base_uri') . config('exact.api.version') . '/';

        if ($division) {
            $baseUri .=  "${division}/";
        }

        parent::__construct(array_filter([
            'headers'  => [
                'Authorization' => "Bearer ${accessToken}",
                'Accept'        => 'application/json',
                'Content-type'  => 'application/json',
            ],
            'base_uri' => $baseUri,
            'handler'  => self::$handler,
        ]));
    }

    /**
     * Set a guzzle stack handler
     *
     * Useful for testing @param HandlerStack|null $handler
     * @see MockHandler
     */
    public static function setHandler(?HandlerStack $handler): void
    {
        self::$handler = $handler;
    }

    /**
     * Creates an ExactApiClient instance with assigned division in the base uri
     *
     * @param AuthServerInterface $authServer
     * @param Token               $token
     * @return static
     */
    public static function createWithDivision(AuthServerInterface $authServer, Token $token): self
    {
        $accessToken = $token->obtainAccessToken($authServer);
        $token->save();

        // we first need a client to fetch the proper division
        $apiClient = new self($accessToken);

        return new self($accessToken, (string) $apiClient->getDivision());
    }

    /**
     * Get current division
     *
     * @return int
     */
    private function getDivision(): int
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
