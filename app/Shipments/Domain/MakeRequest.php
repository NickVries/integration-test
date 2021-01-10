<?php

declare(strict_types=1);

namespace App\Shipments\Domain;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Utils;
use JetBrains\PhpStorm\ArrayShape;
use ODataQuery\ODataResourcePath;

/**
 * @property Client $client
 */
trait MakeRequest
{
    /**
     * @param ODataResourcePath $path
     * @return array
     * @throws GuzzleException
     */
    #[ArrayShape(['d' => 'array'])]
    private function request(
        ODataResourcePath $path
    ): array {
        return Utils::jsonDecode((string) $this->client->get((string) $path)->getBody(), true);
    }
}
