<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function authenticate(Request $request): void
    {
        $authorizationCode = $request->input('code');
        $shopId = $request->input('shop_id');

    }
}
