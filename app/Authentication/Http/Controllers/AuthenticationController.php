<?php

declare(strict_types=1);

namespace App\Authentication\Http\Controllers;

use App\Authentication\Domain\AuthorizationLink;
use App\Authentication\Domain\AuthorizationSession;
use App\Authentication\Domain\AuthServerInterface;
use App\Authentication\Domain\Token;
use App\Authentication\Http\Requests\AuthenticationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use function config;

class AuthenticationController extends Controller
{
    public function init(AuthenticationRequest $request, AuthorizationSession $authorizationSession): JsonResponse
    {
        $sessionToken = $authorizationSession->save($request->shopId(), $request->redirectUri());

        $clientId = config('exact.auth.client_id');
        $exactOnlineRedirectUri = config('exact.auth.redirect_uri');

        $authorizationLink = new AuthorizationLink($clientId, $exactOnlineRedirectUri, $sessionToken);

        return response()->json([
            'data' => [
                'authorization_link' => (string) $authorizationLink->buildUri()
            ]
        ]);
    }

    public function authenticate(AuthenticationRequest $request, AuthServerInterface $authServer): void
    {
        $token = Token::findOrCreate($request->shopId());
        $token->fill($authServer->requestAccessToken($request->code()));
        $token->save();
    }
}
