<?php

namespace App\Rules;

use App\Domains\Blog\BlogService;
use Illuminate\Contracts\Validation\Rule;

class Subdomain implements Rule
{
    private bool $checkUnique;

    private string $message;

    public function __construct(bool $checkUnique = false)
    {
        $this->checkUnique = $checkUnique;
    }

    /**
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (! is_string($value)) {
            $this->message = 'Subdomain should be a string';

            return false;
        }

        /**
         * Can contain a-z 0-9 and hyphen
         * However, hyphen is not allowed in the start and the end
         */
        if (! preg_match('/^[a-z0-9][a-z0-9-]+[a-z0-9]$/i', $value)) {
            $this->message = 'The subdomain is invalid. It should only contain a-z, 0-9, and hyphens. I should not start or end with a hyphen.';

            return false;
        }

        if ($this->checkUnique && BlogService::getBlogBySubdomain($value)) {
            $this->message = 'Subdomain already taken';

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
