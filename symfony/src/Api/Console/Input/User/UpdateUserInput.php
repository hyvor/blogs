<?php

namespace App\Api\Console\Input\User;

use App\Entity\Enum\UserRole;
use App\Entity\Enum\UserStatus;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserInput
{
    public ?int $hyvor_user_id = null;

    public ?UserRole $role = null;

    #[Assert\Choice(choices: [UserStatus::ACTIVE, UserStatus::BLOCKED])]
    public ?UserStatus $status = null;

    #[Assert\Length(max: 255)]
    public ?string $slug = null;

    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[Assert\Length(max: 255)]
    public ?string $website_url = null;

    #[Assert\Length(max: 255)]
    public ?string $picture_url = null;

    public ?string $social_facebook = null;
    public ?string $social_twitter = null;
    public ?string $social_linkedin = null;
    public ?string $social_youtube = null;
    public ?string $social_tiktok = null;
    public ?string $social_instagram = null;
    public ?string $social_github = null;
}
