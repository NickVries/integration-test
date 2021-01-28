<?php

namespace App\Authentication\Http\Requests;

use App\Authentication\Domain\ShopId;
use Illuminate\Foundation\Http\FormRequest;
use Ramsey\Uuid\Uuid;
use function urldecode;

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

    public function sessionToken(): string
    {
        return $this->query('session_token');
    }

    public function code(): string
    {
        return urldecode($this->input('code'));
    }
}
