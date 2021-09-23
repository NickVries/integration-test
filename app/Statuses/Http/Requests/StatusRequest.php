<?php

declare(strict_types=1);

namespace App\Statuses\Http\Requests;

use App\Authentication\Domain\Token;
use Illuminate\Foundation\Http\FormRequest;
use MyParcelCom\Integration\ShopId;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;

class StatusRequest extends FormRequest
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
        $shopId = $this->query('shop_id');

        if (!$shopId) {
            throw new RequestException('Bad request', 'No shop_id provided in the request query', 400);
        }

        try {
            $shopUuid = Uuid::fromString($shopId);
        } catch (InvalidUuidStringException $exception) {
            throw new RequestException('Unprocessable entity', 'shop_id is not a valid UUID', 422);
        }

        return new ShopId($shopUuid);
    }

    public function token(): Token
    {
        $shopId = $this->shopId();
        $token = Token::findByShopId($shopId);

        if (!$token) {
            throw new RequestException(
                'Unauthorized',
                "No access token found for shop ${shopId}. Is shop authenticated?",
                401
            );
        }

        return $token;
    }
}
