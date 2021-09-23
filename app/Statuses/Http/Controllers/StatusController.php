<?php

declare(strict_types=1);

namespace App\Statuses\Http\Controllers;

use App\Statuses\Http\Requests\StatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class StatusController
{
    public function post(
        StatusRequest $request
    ): JsonResponse {
        $shopId = $request->shopId();

        $statusData = $request->getStatusData();
        $shipmentData = $request->getShipmentData();
        $trackingCode = Arr::get($shipmentData, 'attributes.tracking_code');

        // TODO Use the access token to connect to the remote API
        $accessToken = $request->token();

        // TODO Here you can start incorporating logic to update the remote API with the status data

        return response()->json([
            'success' => true,
        ]);
    }
}
