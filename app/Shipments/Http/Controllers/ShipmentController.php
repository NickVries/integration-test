<?php

declare(strict_types=1);

namespace App\Shipments\Http\Controllers;

use App\Shipments\Domain\Order\Order;
use App\Shipments\Domain\Order\OrdersGateway;
use App\Shipments\Http\Requests\ShipmentRequest;
use GuzzleHttp\Exception\GuzzleException;
use MyParcelCom\Integration\Shipment\Shipment;
use MyParcelCom\Integration\ShopId;
use function array_map;
use function config;

class ShipmentController
{
    /**
     * @param OrdersGateway   $ordersGateway
     * @param ShopId          $shopId
     * @param ShipmentRequest $request
     * @return Shipment[]
     * @throws GuzzleException
     */
    public function get(
        OrdersGateway $ordersGateway,
        ShopId $shopId,
        ShipmentRequest $request
    ): array {
        $orders = $ordersGateway->fetchByDateRange($request->shopId(), $request->startDate(), $request->endDate());

        return array_map(
            static fn(Order $order) => $order->toShipment($shopId, config('app.version')),
            $orders
        );
    }
}
