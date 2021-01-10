<?php

declare(strict_types=1);

namespace App\Shipments\Domain\Address;

use App\Http\ExactApiClient;
use App\Shipments\Domain\MakeRequest;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use ODataQuery\ODataResourcePath;
use Ramsey\Uuid\UuidInterface;
use function array_key_exists;

class AddressesGateway
{
    use MakeRequest;

    private const ENTITY = 'crm/Addresses';

    /**
     * In-memory cache for already requested addresses
     *
     * @var Address[]
     */
    private array $addresses = [];

    public function __construct(
        private ExactApiClient $client,
        private AddressFactory $addressFactory
    ) {
    }

    public function fetchOneByAddressId(UuidInterface $id): Address
    {
        if (!array_key_exists($id->toString(), $this->addresses)) {
            try {
                $response = $this->request(new ODataResourcePath(self::ENTITY . "(guid'${id}')"));
            } catch (GuzzleException $e) {
                return $this->addressFactory->createNullAddress();
            }
            $this->addresses[$id->toString()] = $this->addressFactory->createFromArray(
                (array) Arr::get($response, 'd', [])
            );
        }

        return $this->addresses[$id->toString()];
    }
}
