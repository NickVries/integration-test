<?php

namespace App\Shipments\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

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
}
