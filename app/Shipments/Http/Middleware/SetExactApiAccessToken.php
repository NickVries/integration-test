<?php

declare(strict_types=1);

namespace App\Shipments\Http\Middleware;

use App\Authentication\Domain\AuthServerInterface;
use App\Authentication\Domain\ShopId;
use App\Authentication\Domain\Token;
use App\Http\ExactApiClient;
use App\Http\ExactApiDivisionClient;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use function response;

class SetExactApiAccessToken
{
    public function __construct(
        private Container $container
    )
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if (!$request->query('shop_id')) {
            return $this->missingShopIdResponse();
        }

        try {
            $shopId = new ShopId(Uuid::fromString($request->query('shop_id')));
        } catch (InvalidUuidStringException $exception) {
            return $this->invalidUuidResponse();
        }

        $token = Token::findByShopId($shopId);

        if (!$token) {
            return $this->unauthorizedResponse($shopId);
        }

        $this->container->singleton(
            ExactApiDivisionClient::class,
            fn(Container $container) => $this->createApiDivisionClient($token, $container)
        );

        $this->container->singleton(ShopId::class, fn() => $shopId);

        return $next($request);
    }

    private function unauthorizedResponse(ShopId $shopId): JsonResponse
    {
        return response()->json([
            'errors' => [
                [
                    'status' => '401',
                    'title'  => 'Unauthorized',
                    'detail' => "No ExactOnline.nl API access token found for shop ${shopId}. Is shop authenticated?",
                ],
            ],
        ], Response::HTTP_UNAUTHORIZED);
    }

    private function missingShopIdResponse(): JsonResponse
    {
        return response()->json([
            'errors' => [
                [
                    'status' => '400',
                    'title'  => 'Bad request',
                    'detail' => "No shop_id provided in the request query",
                ],
            ],
        ], Response::HTTP_BAD_REQUEST);
    }

    private function invalidUuidResponse(): JsonResponse
    {
        return response()->json([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable entity',
                    'detail' => "shop_id is not a valid UUID",
                ],
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function createApiDivisionClient(Token $token, Container $container): ExactApiDivisionClient
    {
        $accessToken = $token->obtainAccessToken($container->get(AuthServerInterface::class));
        $token->save();

        $apiClient = new ExactApiClient($accessToken);

        return new ExactApiDivisionClient($accessToken, (string) $apiClient->getDivision());
    }
}
