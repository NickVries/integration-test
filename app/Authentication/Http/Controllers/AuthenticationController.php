<?php

declare(strict_types=1);

namespace App\Authentication\Http\Controllers;

use App\Authentication\Domain\AuthServerInterface;
use App\Authentication\Domain\Token;
use App\Authentication\Http\Requests\AuthenticationRequest;
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
