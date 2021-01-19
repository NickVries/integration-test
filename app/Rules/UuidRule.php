<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Ramsey\Uuid\Uuid as RamseyUuid;

class UuidRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return RamseyUuid::isValid($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Invalid UUID';
    }
}
