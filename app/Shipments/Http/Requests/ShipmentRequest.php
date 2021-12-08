<?php

namespace App\Shipments\Http\Requests;

use App\Authentication\Domain\Token;
use App\Exceptions\RequestInputException;
use App\Exceptions\RequestUnauthorizedException;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use MyParcelCom\Integration\ShopId;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;

class ShipmentRequest extends FormRequest
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

    public function startDate(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->input('filter.start_date') . ' 00:00:00');
    }

    public function endDate(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->input('filter.end_date') . ' 23:59:59');
    }

    public function shopId(): ShopId
    {
        $shopId = $this->query('shop_id');

        if (!$shopId) {
            throw new RequestInputException('Bad request', 'No shop_id provided in the request query');
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

    /**
     * @return int|null
     */
    public function pageNumber(): ?int
    {
        return (int) $this->query('pageNumber');
    }

    /**
     * @return int|null
     */
    public function pageSize(): ?int
    {
        return (int) $this->query('pageSize');
    }
}
