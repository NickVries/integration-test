<?php

declare(strict_types=1);

namespace App\Shipments\Http\Controllers;

use App\Authentication\Domain\AuthServerInterface;
use App\Http\ExactApiClient;
use App\Shipments\Domain\Order\Order;
use App\Shipments\Domain\Order\OrdersGateway;
use App\Shipments\Http\Requests\ShipmentRequest;
use GuzzleHttp\Exception\GuzzleException;
use MyParcelCom\Integration\Shipment\Shipment;
use function array_map;
use function config;

class ShipmentController
{
    /**
     * @param OrdersGateway       $ordersGateway
     * @param ShipmentRequest     $request
     * @param AuthServerInterface $authServer
     * @return Shipment[]
     * @throws GuzzleException
     */
    public function get(
        OrdersGateway $ordersGateway,
        ShipmentRequest $request,
        AuthServerInterface $authServer
    ): array {

        $client = ExactApiClient::createWithDivision($authServer, $request->token());
        $shopId = $request->shopId();
        $startDate = $request->startDate();
        $endDate = $request->endDate();

        $orders = $ordersGateway->fetchByDateRange($shopId, $startDate, $endDate, $client);

        return array_map(
            static fn(Order $order) => $order->toShipment($shopId, config('app.version')),
            $orders
        );
    }
}
