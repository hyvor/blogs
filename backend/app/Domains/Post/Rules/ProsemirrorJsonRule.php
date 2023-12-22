<?php declare(strict_types=1);

namespace App\Domains\Post\Rules;

use App\Domains\Post\Content\PostContentService;
use App\Models\Blog;
use Closure;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;
use Illuminate\Contracts\Validation\ValidationRule;

class ProsemirrorJsonRule implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail) : void
    {
        if (is_string($value)) {
            try {
                PostContentService::getDocumentFromJson($value, new Blog);
            } catch (PhrosemirrorException $e) {
                $fail('Prosemirror JSON error:' . $e->getMessage());
            }
        }
    }

}