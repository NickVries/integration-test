<?php

declare(strict_types=1);

namespace App\Shipments\Domain;

use Carbon\Carbon;
use Faker\Generator;
use MyParcelCom\Integration\Shipment\Address;
use MyParcelCom\Integration\Shipment\Customs\ContentType;
use MyParcelCom\Integration\Shipment\Customs\Customs;
use MyParcelCom\Integration\Shipment\Customs\Incoterm;
use MyParcelCom\Integration\Shipment\Customs\NonDelivery;
use MyParcelCom\Integration\Shipment\Items\Item;
use MyParcelCom\Integration\Shipment\Items\ItemCollection;
use MyParcelCom\Integration\Shipment\PhysicalProperties;
use MyParcelCom\Integration\Shipment\Price;
use MyParcelCom\Integration\Shipment\Shipment;
use MyParcelCom\Integration\ShopId;

class ShipmentFactory
{
    public function __construct(private Generator $faker)
    {
    }

    public function create(int $count, ShopId $shopId, Carbon $startDate, Carbon $endDate): array
    {
        $shipments = [];

        for ($i = 0; $i < $count; $i++) {
            $shipments[] = $this->createShipment($shopId, $startDate, $endDate);
        }

        return $shipments;
    }

    private function createShipment(ShopId $shopId, Carbon $startDate, Carbon $endDate): Shipment
    {
        return new Shipment(
            shopId: $shopId,
            recipientAddress: new Address(
                street1: $this->faker->streetName,
                city: $this->faker->city,
                countryCode: $this->faker->countryCode,
                firstName: $this->faker->firstName,
                lastName: $this->faker->lastName,
                streetNumber: (int) $this->faker->buildingNumber,
                postalCode: $this->faker->postcode,
                email: $this->faker->email,
                phoneNumber: $this->faker->phoneNumber,
            ),
            description: $this->faker->sentence,
            customerReference: $this->faker->sentence,
            channel: config('app.channel'),
            totalValue: new Price($this->faker->numberBetween(100, 30000), $this->faker->currencyCode),
            price: new Price($this->faker->numberBetween(100, 30000), $this->faker->currencyCode),
            physicalProperties: new PhysicalProperties(
                weight: $this->faker->numberBetween(1000, 25000),
                height: $this->faker->numberBetween(100, 1000),
                width: $this->faker->numberBetween(100, 1000),
                length: $this->faker->numberBetween(100, 1000),
            ),
            items: new ItemCollection(
                new Item(
                    description: $this->faker->sentence,
                    quantity: $this->faker->randomDigitNotZero(),
                    sku: $this->faker->hexColor,
                    itemValue: new Price($this->faker->numberBetween(100, 30000), $this->faker->currencyCode),
                    hsCode: (string) $this->faker->randomNumber(6),
                    itemWeight: $this->faker->numberBetween(200, 2000),
                    originCountryCode: $this->faker->countryCode,
                ),
            ),
            createdAt: $this->faker->dateTimeBetween($startDate, $endDate),
            customs: new Customs(
                contentType: $this->faker->randomElement(ContentType::values()),
                invoiceNumber: (string) $this->faker->randomNumber(8),
                nonDelivery: $this->faker->randomElement(NonDelivery::values()),
                incoterm: $this->faker->randomElement(Incoterm::values()),
                shippingValue: new Price($this->faker->numberBetween(100, 30000), $this->faker->currencyCode),
                licenseNumber: (string) $this->faker->randomNumber(8),
                certificateNumber: (string) $this->faker->randomNumber(8),
            )
        );
    }
}
