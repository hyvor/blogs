<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * This is a helper class
 * Probably should be included in Laravel (maybe there is, but couldn't find any)
 *
 * This can be used as a OR between two rules
 */

class OneOf implements Rule
{
    /**
     * @var Rule[]
     */
    private array $rules;

    /**
     * @var Rule[]
     */
    private array $failedRules;

    public function __construct(Rule|string ...$rules)
    {
        $this->rules = $rules;
    }

    public function passes($attribute, $value)
    {
        foreach ($this->rules as $rule) {
            try {
                Validator::validate([$attribute => $value], [$attribute => $rule]);

                return true;
            } catch (ValidationException) {
                $this->failedRules[] = $rule;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->failedRules[0]->message();
    }
}
