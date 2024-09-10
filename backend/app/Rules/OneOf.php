<?php declare(strict_types=1);

namespace App\Rules;

use Exception;
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
     * @var array<Rule|string>
     */
    private array $rules;

    /**
     * @var array<Rule|string>
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
            } catch (Exception) {
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
        if ($this->failedRules[0] instanceof Rule) {
            $message = $this->failedRules[0]->message();
            return is_array($message) ? implode(', ', $message) : strval($message);
        }
        return 'The :attribute is invalid.';
    }
}
