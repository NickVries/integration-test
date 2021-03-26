<?php

declare(strict_types=1);

use App\Authentication\Http\Controllers\AuthenticationController;
use App\Shipments\Http\Controllers\ShipmentController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => new JsonResponse([
    'meta' => [
        'title'  => 'MyParcel.com integration microservice for ExactOnline.nl',
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
