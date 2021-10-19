<?php

declare(strict_types=1);

use App\Authentication\Http\Controllers\AuthenticationController;
use App\Shipments\Http\Controllers\ShipmentController;
use App\Statuses\Http\Controllers\ShipmentStatusCallbackController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => new JsonResponse([
    'meta' => [
        'title'  => 'MyParcel.com ' . config('app.name') . ' (' . config('app.channel') . ')',
        'status' => 'OK',
    ],
]));

Route::group(
    ['prefix' => 'public'],
    function () {
        Route::post('init-auth', AuthenticationController::class . '@init')->name('init-auth');
        Route::get('authenticate', AuthenticationController::class . '@authenticate')->name('authenticate');
    }
);

Route::get('shipments', ShipmentController::class . '@get')
    ->name('get-shipments')
    ->middleware('transform_to_json_api');

Route::post('callback/shipment-statuses', ShipmentStatusCallbackController::class . '@post')
    ->name('shipment-statuses')
    ->middleware('matching_channel_only:' . config('app.channel'));
