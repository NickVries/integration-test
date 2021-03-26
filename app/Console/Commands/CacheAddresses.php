<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Authentication\Domain\Token;
use App\Shipments\Domain\Address\AddressesGateway;
use Illuminate\Console\Command;

class CacheAddresses extends Command
{
    protected $signature = 'exact:cache:addresses';

    protected $description = 'Will access all addresses created in the last 24 hours and proactively cache them.';

    public function handle(AddressesGateway $addressesGateway): void
    {
        Token::all()->each(function (Token $token) {

        });
    }
}
