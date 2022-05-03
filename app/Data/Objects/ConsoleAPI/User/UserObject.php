<?php

namespace App\Data\Objects\ConsoleAPI\User;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\Language;
use App\Models\Blog;

class UserObject
{
    public int $id;
    public int $created_at;
    public int $updated_at;
    public ?int $hyvor_user_id;

    public UserStatusEnum $status;
    public UserRoleEnum $role;
    public string $slug;
    public string $email;
    
    public ?string $picture_url; 
    public ?string $website_url;

    public ?string $social_facebook;
    public ?string $social_twitter; 
    public ?string $social_linkedin; 
    public ?string $social_youtube;
    public ?string $social_instagram;
    public ?string $social_github;

    public function __construct(User $user, Blog $blog)
    {
        $this->id = $user->id;
        $this->created_at = $user->created_at->timestamp;
        $this->updated_at = $user->updated_at->timestamp;
        $this->hyvor_user_id = $user->hyvor_user_id;

        $this->status = $user->status;
        
        $this->role = $user->role;
        $this->slug = $user->slug;
        $this->email = $user->email;

        $this->picture_url = $user->picture_url;
        $this->website_url = $user->website_url;

        $this->social_facebook = $user->social_facebook;
        $this->social_twitter = $user->social_twitter;
        $this->social_linkedin = $user->social_linkedin;
        $this->social_youtube = $user->social_youtube;
        $this->social_instagram = $user->social_instagram;
        $this->social_github = $user->social_github;
        
        $this->variants = $user->variants->map(function($variant) use ($blog) {
            return new UserVariantObject($variant, $blog);
        })->keyBy('language_id');
        
    }
} 
