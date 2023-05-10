<?php

declare(strict_types=1);

namespace App\Shipments\Http\Controllers;

use App\Shipments\Domain\ShipmentFactory;
use App\Shipments\Http\Requests\ShipmentRequest;
use JetBrains\PhpStorm\ArrayShape;

class ShipmentController
{
    #[ArrayShape([
        'items'         => "\MyParcelCom\Integration\Shipment\Shipment[]",
        'total_records' => "int",
        'total_pages'   => "int",
    ])]
    public function get(ShipmentRequest $request, ShipmentFactory $shipmentFactory): array
    {
        $shopId = $request->shopId();

        $shipments = $shipmentFactory->create(
            30,
            $shopId,
            $request->startDate(),
            $request->endDate()
        );

        return [
            'items'         => $shipments,
            'total_records' => 30,
            'total_pages'   => 30,
        ];
    }
}
