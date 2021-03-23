<?php

declare(strict_types=1);

namespace App\Shipments\Http\Controllers;

use App\Shipments\Domain\Order\Order;
use App\Shipments\Domain\Order\OrdersGateway;
use App\Shipments\Http\Requests\ShipmentRequest;
use Closure;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use MyParcelCom\Integration\ShopId;
use function array_map;
use function config;
use function response;

class ShipmentController
{
    /**
     * @param OrdersGateway   $ordersGateway
     * @param ShopId          $shopId
     * @param ShipmentRequest $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function get(
        OrdersGateway $ordersGateway,
        ShopId $shopId,
        ShipmentRequest $request
    ): JsonResponse {
        $orders = $ordersGateway->fetchByDateRange($request->shopId(), $request->startDate(), $request->endDate());

        return response()->json([
            'data' => array_map($this->transformer($shopId), $orders)
        ]);
    }

    private function transformer(ShopId $shopId): Closure
    {
        return static fn(Order $order) => $order->toShipment($shopId, config('app.version'))->transformToJsonApiArray();
    }
}
