<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BlogName implements Rule
{
    private string $message;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        // name cannot be null

        if (! is_string($value) || trim($value) === '') {
            $this->message = 'Blog name cannot be empty';

            return false;
        }

        if (mb_strlen($value) > config('limits.max_blog_name_length')) {
            $this->message = 'Blog name is too long';

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
