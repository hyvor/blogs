<?php

namespace App\Data\Objects\ConsoleAPI\User;

use App\Models\User;
use App\Models\Language;
use App\Models\Blog;

class UserObject
{
    public int $id;
    public int $created_at;
    public int $updated_at;
    public int $blog_id;
    public int $user_id;
    public bool $is_synced;
    public string $status;
    public string $role;

    public string $slug;
    public string $email; 
    public ?string $picture; 
    public ?string $url; 

    public ?string $social_facebook;
    public ?string $social_twitter; 
    public ?string $social_linkedin; 
    public ?string $social_youtube; 
    public ?string $social_instagram;

    public function __construct(User $user, Blog $blog)
    {
        $this->id = $user->id;
        $this->created_at = $user->created_at->timestamp;
        $this->updated_at = $user->updated_at->timestamp;        
        $this->blog_id = $user->blog_id;

        $this->user_id = $user->user_id;
        $this->is_synced = $user->is_synced;
        $this->status = $user->status;
        $this->role = $user->role;

        $this->slug = $user->slug;
        $this->email = $user->email;
        $this->picture = $user->picture;
        $this->url = $user->url;

        $this->social_facebook = $user->social_facebook;
        $this->social_twitter = $user->social_twitter;
        $this->social_linkedin = $user->social_linkedin;
        $this->social_youtube = $user->social_youtube;
        $this->social_instagram = $user->social_instagram;
        
        $this->variants = $user->variants->map(function($variant) use ($blog) {
            return new UserVariantObject($variant, $blog);
        })->keyBy('language_id');
        
    }
} 
