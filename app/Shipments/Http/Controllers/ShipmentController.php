<?php

declare(strict_types=1);

namespace App\Shipments\Http\Controllers;

use App\Shipments\Http\Requests\ShipmentRequest;
use Carbon\Carbon;
use MyParcelCom\Integration\Shipment\Address;
use MyParcelCom\Integration\Shipment\Items\Item;
use MyParcelCom\Integration\Shipment\Items\ItemCollection;
use MyParcelCom\Integration\Shipment\PhysicalProperties;
use MyParcelCom\Integration\Shipment\Price;
use MyParcelCom\Integration\Shipment\Shipment;
use function config;

class ShipmentController
{
    /**
     * @param ShipmentRequest $request
     * @return Shipment[]
     */
    public function get(ShipmentRequest $request): array
    {
        // TODO Shop UUID is always provided and you should use it to distinguish
        // TODO between different auth sessions
        $shopId = $request->shopId();

        // TODO Use the access token to connect to the remote API from where orders are fetched
        $accessToken = $request->token();

        // TODO Use $request->startDate() and $request->endDate() to obtain the request date range for the orders
        $startDate = $request->startDate();
        $endDate = $request->endDate();

        // TODO use the number and size as pagination for the integration
        $pageNumber = $request->pageNumber();
        $pageSize = $request->pageSize();

        // TODO Here you can start incorporating logic that converts orders from the remote API into Shipment objects
        return [
            // This is an example shipment
            new Shipment(
                shopId: $shopId,
                createdAt: Carbon::createFromTimeString('2020-01-01 12:30:00'),
                recipientAddress: new Address(
                    street1: 'Baker St',
                    streetNumber: 221,
                    streetNumberSuffix: 'b',
                    postalCode: 'NW1 6XE',
                    city: 'London',
                    countryCode: 'GB',
                    firstName: 'Sherlock',
                    lastName: 'Holmes',
                    company: '',
                    email: 'sherlock@holmes.com',
                    phoneNumber: '+123456789',
                ),
                description: 'Google Chromecast Ultra',
                customerReference: '#1234567890',
                channel: config('app.channel'),
                totalValue: new Price(4000, 'EUR'), // amount is in cents
                price: new Price(4000, 'EUR'),
                physicalProperties: new PhysicalProperties(
                    weight: 500,
                    height: 30,
                    width: 20,
                    length: 25,
                ),
                items: new ItemCollection(
                    new Item(
                        description: 'A google chromecast with 4K support',
                        quantity: 1,
                        sku: '',
                        itemValue: new Price(4000, 'EUR'),
                        hsCode: '',
                        itemWeight: 500,
                        originCountryCode: 'US',
                    ),
                ),
            ),
            // This is another example shipment
            new Shipment(
                shopId: $shopId,
                createdAt: Carbon::createFromTimeString('2020-01-13 20:10:00'),
                recipientAddress: new Address(
                    street1: 'Bell St',
                    streetNumber: 27,
                    postalCode: 'NW1 5BY',
                    city: 'London',
                    countryCode: 'GB',
                    firstName: 'James',
                    lastName: 'Bond',
                    email: 'james@bond.com',
                    phoneNumber: '+123456789',
                ),
                description: 'Google Stadia Controller',
                customerReference: '#XYZ12345',
                channel: config('app.channel'),
                totalValue: new Price(80000, 'GBP'),
                price: new Price(80000, 'GBP'),
                physicalProperties: new PhysicalProperties(
                    weight: 2500,
                    height: 300,
                    width: 100,
                    length: 100,
                ),
                items: new ItemCollection(
                    new Item(
                        description: 'Game controller',
                        quantity: 1,
                        sku: '',
                        itemValue: new Price(80000, 'GBP'),
                        hsCode: '',
                        itemWeight: 2500,
                        originCountryCode: 'US',
                    ),
                ),
            ),
        ];
    }
}
