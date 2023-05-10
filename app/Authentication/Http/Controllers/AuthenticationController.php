<?php

declare(strict_types=1);

namespace App\Authentication\Http\Controllers;

use App\Authentication\Domain\AuthorizationSession;
use App\Authentication\Http\Requests\InitAuthRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use function response;

class AuthenticationController extends Controller
{
    public function init(
        InitAuthRequest $request,
        AuthorizationSession $authorizationSession
    ): JsonResponse {
        $shopId = $request->shopId();
        $finalRedirectUri = $request->redirectUri();

        $sessionToken = $authorizationSession->save($shopId, $finalRedirectUri);

        $redirectUri = route('auth')
            . '?redirect_uri='
            . config('services.remote.oauth2.redirect_uri')
            . '?session_token=' . $sessionToken;

        return response()->json([
            'data' => [
                'authorization_link' => $redirectUri,
            ],
        ]);
    }

    public function authenticate(Request $request, AuthorizationSession $authorizationSession): RedirectResponse
    {
        $sessionToken = $request->query('session_token');

        $session = $authorizationSession->fetch($sessionToken);

        return response()->redirectTo($session['redirect_uri']);
    }
}
