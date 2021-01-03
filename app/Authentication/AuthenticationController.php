<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Http\Controllers\Controller;

class AuthenticationController extends Controller
{
    public function authenticate(AuthenticationRequest $request, AuthServerInterface $authServer): void
    {
        $token = Token::findOrCreate($request->shopId());
        $token->fill($authServer->requestAccessToken($request->code()));
        $token->save();
    }
}
