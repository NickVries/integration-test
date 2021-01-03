<?php

declare(strict_types=1);

use App\Authentication\AuthenticationController;
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
        Route::post('authenticate', AuthenticationController::class . '@authenticate')->name('authenticate');
    }
);
