<?php

namespace App\Domain\User\ValidationRules;

use Laravel\Fortify\Rules\Password;

/**
 * Trait PasswordValidationRules
 */
trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', new Password(), 'confirmed'];
    }
}
