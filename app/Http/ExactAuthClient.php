<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use function config;

class ExactAuthClient extends Client
{
    public function __construct()
    {
        parent::__construct([
            'base_uri' => config('exact.api.base_uri'),
        ]);
    }
}
