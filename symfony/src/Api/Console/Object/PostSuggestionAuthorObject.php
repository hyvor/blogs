<?php

namespace App\Api\Console\Object;

use App\Entity\User;

/**
 * Backs @hyvor/richtext's resolveAuthor plugin config - turns a `user:<hyvor_user_id>`
 * Author id into a display {name, picture}. Built from a blog-scoped User lookup rather
 * than trusted client input, so a suggestion author who is no longer a user of this blog
 * (removed, or never was) resolves to a clearly-labeled placeholder instead of silently
 * showing nothing or another blog's user.
 */
class PostSuggestionAuthorObject
{
    public ?string $name;
    public ?string $picture_url;

    private function __construct(?string $name, ?string $picture_url)
    {
        $this->name = $name;
        $this->picture_url = $picture_url;
    }

    public static function fromUser(User $user, ?string $name): self
    {
        return new self($name, $user->getPictureUrl());
    }

    public static function deletedUser(): self
    {
        return new self('Deleted user', null);
    }
}
