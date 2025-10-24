<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NciSenegalaisRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Format NCI sénégalais : 13 chiffres commençant par 1 ou 2
        $pattern = '/^[12]\d{12}$/';

        if (!preg_match($pattern, $value)) {
            $fail('Le numéro NCI doit être un numéro sénégalais valide (13 chiffres commençant par 1 ou 2).');
        }
    }
}
