<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class isValidPasswordRule implements Rule
{
    /**
     * Determine if the Length Validation Rule passes.
     *
     * @var bool
     */
    public $lengthPasses = true;

    /**
     * Determine if the Uppercase Validation Rule passes.
     *
     * @var bool
     */
    public $uppercasePasses = true;

    /**
     * Determine if the Numeric Validation Rule passes.
     *
     * @var bool
     */
    public $numericPasses = true;

    /**
     * Determine if the Special Character Validation Rule passes.
     *
     * @var bool
     */
    public $specialCharacterPasses = true;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->lengthPasses = (Str::length($value) >= 8);
        $this->uppercasePasses = (Str::lower($value) !== $value);
        $this->numericPasses = ((bool) preg_match('/[0-9]/', $value));
        $this->specialCharacterPasses = ((bool) preg_match('/[^A-Za-z0-9]/', $value));

        return $this->lengthPasses && $this->uppercasePasses && $this->numericPasses && $this->specialCharacterPasses;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        switch (true) {
            case ! $this->uppercasePasses
            && $this->numericPasses
            && $this->specialCharacterPasses:
                return __('password.case_1');

            case ! $this->numericPasses
            && $this->uppercasePasses
            && $this->specialCharacterPasses:
                return __('password.case_2');

            case ! $this->specialCharacterPasses
            && $this->uppercasePasses
            && $this->numericPasses:
                return __('password.case_3');

            case ! $this->uppercasePasses
            && ! $this->numericPasses
            && $this->specialCharacterPasses:
                return __('password.case_4');

            case ! $this->uppercasePasses
            && ! $this->specialCharacterPasses
            && $this->numericPasses:
                return __('password.case_5');

            case ! $this->uppercasePasses
            && ! $this->numericPasses
            && ! $this->specialCharacterPasses:
                return __('password.case_6');

            default:
                return __('password.case_7');
        }
    }
}
