<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    public static function strong(): Password
    {
        return Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols();
    }

    /** @return array<int, string|\Illuminate\Validation\Rules\Password> */
    public static function validationRules(bool $confirmed = true): array
    {
        $rules = ['required', 'string', self::strong()];

        if ($confirmed) {
            $rules[] = 'confirmed';
        }

        return $rules;
    }
}
