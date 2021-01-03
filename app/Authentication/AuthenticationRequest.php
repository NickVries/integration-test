<?php

namespace App\Authentication;

use Illuminate\Foundation\Http\FormRequest;
use Ramsey\Uuid\Uuid;

class AuthenticationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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

    public function shopId(): ShopId
    {
        return new ShopId(Uuid::fromString($this->input('shop_id')));
    }

    public function code(): string
    {
        return $this->input('code');
    }
}
