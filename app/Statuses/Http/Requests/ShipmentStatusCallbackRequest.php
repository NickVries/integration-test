<?php

declare(strict_types=1);

namespace App\Statuses\Http\Requests;

use App\Authentication\Domain\Token;
use App\Exceptions\RequestInputException;
use App\Exceptions\RequestUnauthorizedException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MyParcelCom\Integration\ShopId;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;

class ShipmentStatusCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    public function getShipmentData(): array
    {
        $included = $this->get('included');

        return collect($included)->first(fn ($include) => $include['type'] === 'shipments');
    }

    public function getStatusData(): array
    {
        $included = $this->get('included');

        return collect($included)->first(fn ($include) => $include['type'] === 'statuses');
    }

    public function shopId(): ShopId
    {
        $shopId = Arr::get($this->getShipmentData(), 'relationships.shop.data.id');

        if (!$shopId) {
            throw new RequestInputException('Bad request', 'No shop_id provided in the request body');
        }

        try {
            $shopUuid = Uuid::fromString($shopId);
        } catch (InvalidUuidStringException $exception) {
            throw new RequestInputException('Unprocessable entity', 'shop_id is not a valid UUID', 422);
        }

        return new ShopId($shopUuid);
    }

    public function token(): Token
    {
        $shopId = $this->shopId();
        $token = Token::findByShopId($shopId);

        if (!$token) {
            throw new RequestUnauthorizedException(
                'Unauthorized',
                "No access token found for shop ${shopId}. Is shop authenticated?"
            );
        }

        return $token;
    }
}
