<?php

declare(strict_types=1);

namespace App\Authentication\Http\Controllers;

use App\Authentication\Domain\AuthorizationLink;
use App\Authentication\Domain\AuthorizationSession;
use App\Authentication\Domain\AuthServerInterface;
use App\Authentication\Domain\Token;
use App\Authentication\Http\Requests\AuthenticationRequest;
use App\Authentication\Http\Requests\InitAuthRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use function config;
use function response;

class AuthenticationController extends Controller
{
    public function init(InitAuthRequest $request, AuthorizationSession $authorizationSession): JsonResponse
    {
        //
        // The code in this method is an example for providing a working authorization code grant
        // type links with 'double' redirect.
        //
        // Why 'double' redirects?
        // OAuth 2.0 authorization servers that support authorization code grant types have the clients bundled with
        // a specific redirect URI or a set of redirect URIs. Whenever the authorization link (which will include the
        // redirect uri) is visited the authorization server will validate it.
        // MyParcel.com handles different customer domains which makes the support for a limited set of redirect URIs
        // difficult. Therefore in this controller we introduce the idea of double redirects that will hide the final
        // redirect in a session. The integrated platform's authorization server will always redirect back to
        // the AuthenticationController::authenticate() action which will then save the access token and redirect
        // to the original MyParcel.com redirect URI.
        //

        // We use the MyParcel.com shop UUID to identify the authorization session
        $shopId = $request->shopId();
        // The final redirect URI that will lead the user to the proper MyParcel.com origin
        $finalRedirectUri = $request->redirectUri();

        // Building authorization code grant links requires the OAuth2 Client ID.
        // We use the client ID saved in the configuration. Please replace 'remote' with the name
        // of the platform you are integrating with. See config/services.php too.
        $clientId = config('services.remote.oauth2.client_id');

        // We create a session which will hold the final redirect URI
        $sessionToken = $authorizationSession->save($shopId, $finalRedirectUri);

        // This redirect URI is the one we will send to the remote platform alongside with a session token
        // which is later used to unlock the final redirect uri
        $redirectUri = config('services.remote.oauth2.redirect_uri');

        $authorizationLink = new AuthorizationLink($clientId, $redirectUri, $sessionToken);

        return response()->json([
            'data' => [
                'authorization_link' => (string) $authorizationLink->buildUri(),
            ],
        ]);
    }

    public function authenticate(
        AuthenticationRequest $request,
        AuthorizationSession $authorizationSession,
        AuthServerInterface $authServer
    ): RedirectResponse {
        //
        // After successful authorization is performed by the remote authorization server the customer is redirected
        // to a route handled by this method.
        //

        // First, we unpack the session previously started by AuthenticationController::init() in order to get
        // the shop UUID and the final redirect uri
        $sessionToken = $request->sessionToken();
        $payload = $authorizationSession->fetch($sessionToken);
        $shopId = $payload['shop_id'];
        $redirectUri = $payload['redirect_uri'];

        // Second, we find (or create) a new access token record in the local database
        // and we make sure that the token is erased (if existed before)
        $token = Token::findOrCreate($shopId);
        $token->nullify();

        // Third, we request a new access token
        // TODO Expand upon the AuthServer class to satisfy the logic below
        $authServerResponse = $authServer->requestAccessToken(
            $request->code(),
            config('services.remote.oauth2.client_id') . "?session_token=${sessionToken}"
        );

        // Next, we save the acquired access token (alongside refresh token and other meta data) into the database
        $token->fill($authServerResponse);
        $token->save();

        // Finally, we redirect the user to the original MyParcel.com redirect URI
        return response()->redirectTo($redirectUri);
    }
}
