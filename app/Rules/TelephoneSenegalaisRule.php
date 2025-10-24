<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelephoneSenegalaisRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Format sénégalais : +221 77/78/76/70 XXX XXX ou 77/78/76/70 XXX XXX
        $pattern = '/^(?:\+221\s?)?(?:77|78|76|70)\s?\d{3}\s?\d{2}\s?\d{2}$/';

        if (!preg_match($pattern, $value)) {
            $fail('Le numéro de téléphone doit être un numéro sénégalais valide (ex: +221771234567 ou 771234567).');
        }
    }
}
