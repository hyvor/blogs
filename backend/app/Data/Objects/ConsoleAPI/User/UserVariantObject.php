<?php

namespace App\Data\Objects\ConsoleAPI\User;

use App\Domains\Route\PermalinkRepository;
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\User;
use App\Models\UserVariant;

class UserVariantObject
{
    public int $user_id;

    public int $language_id;

    public string $url;

    public ?string $name;

    public ?string $bio;

    public ?string $location;

    public function __construct(UserVariant $variant, User $user, Blog $blog)
    {
        $this->user_id = $variant->user_id;
        $this->language_id = $variant->language_id;

        $language = $variant->language;

        if (!$language) {
            throw new SafetyException('UserVariantObject: Language not found');
        }

        $this->url = PermalinkRepository::getAuthorPermalink($user, $blog, $language);

        $this->name = $variant->name;
        $this->bio = $variant->bio;
        $this->location = $variant->location;
    }
}
