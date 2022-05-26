<?php

namespace App\Rules;

use App\Models\Blog;
use Illuminate\Contracts\Validation\Rule;

/**
 * A simple check to make sure a string is a path
 * Starts with /
 * And, has no whitespaces
 */

class RedirectPath implements Rule
{

    private Blog $blog;
    private string $message;

    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
    }

    public function passes($attribute, $value) : bool
    {

        if (!is_string($value)) {
            $this->message = 'Path should be a string';
            return false;
        }

        if (preg_match('/^\/[^\s]+$/', $value) !== 1) {
            $this->message = 'Path is invalid';
            return false;
        }

        if ($this->blog->redirects()->where('path', $value)->exists()) {
            $this->message = 'A redirect already exists for the path';
            return false;
        }

        return true;

    }

    public function message()
    {
        return $this->message;
    }
}
