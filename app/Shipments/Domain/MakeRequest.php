<?php

declare(strict_types=1);

namespace App\Shipments\Domain;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Utils;
use JetBrains\PhpStorm\ArrayShape;
use ODataQuery\ODataResourcePath;

trait MakeRequest
{
    /**
     * @param ODataResourcePath $path
     * @param Client            $client
     * @return array
     * @throws GuzzleException
     */
    #[ArrayShape(['d' => 'array'])]
    private function request(
        ODataResourcePath $path,
        Client $client
    ): array {
        return Utils::jsonDecode((string) $client->get((string) $path)->getBody(), true);
    }
}
