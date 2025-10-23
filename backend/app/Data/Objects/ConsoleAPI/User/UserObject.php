<?php
declare(strict_types=1);

namespace App\Data\Objects\ConsoleAPI\User;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Models\Blog;
use App\Models\User;

class UserObject
{
    public int $id;

    public int $created_at;

    public int $updated_at;

    public ?int $hyvor_user_id;

    public UserStatusEnum $status;

    public UserRoleEnum $role;

    public string $slug;

    public int $posts_count;

    public ?string $email;

    public ?string $picture_url;

    public ?string $website_url;

    public ?string $social_facebook;

    public ?string $social_twitter;

    public ?string $social_linkedin;

    public ?string $social_youtube;

    public ?string $social_tiktok;

    public ?string $social_instagram;

    public ?string $social_github;

    /**
     * @var UserVariantObject[]
     */
    public array $variants;

    public function __construct(User $user, Blog $blog)
    {
        $this->id = $user->id;
        $this->created_at = ($user->created_at ?? now())->getTimestamp();
        $this->updated_at = ($user->updated_at ?? now())->getTimestamp();
        $this->hyvor_user_id = $user->hyvor_user_id;

        $this->status = $user->status;
        $this->role = $user->role;
        $this->slug = $user->slug;
        $this->posts_count = $user->posts_count;

        $this->email = $this->getUserEmail($user);

        $this->picture_url = $user->picture_url;
        $this->website_url = $user->website_url;

        $this->social_facebook = $user->social_facebook;
        $this->social_twitter = $user->social_twitter;
        $this->social_linkedin = $user->social_linkedin;
        $this->social_youtube = $user->social_youtube;
        $this->social_tiktok = $user->social_tiktok;
        $this->social_instagram = $user->social_instagram;
        $this->social_github = $user->social_github;

        /** @var UserVariantObject[] $variants */
        $variants = $user->variants
            ->map(fn($variant) => new UserVariantObject($variant, $user, $blog))
            ->sortBy('language_id')
            ->values()
            ->toArray();

        $this->variants = $variants;
    }

    private function getUserEmail(User $user): ?string
    {
        if ($user->status === UserStatusEnum::INVITED) {
            // mask the email
            $email = $user->email;

            if (!$email) {
                return null;
            }

            $emailParts = explode('@', $email);
            $emailParts[0] = substr($emailParts[0], 0, 3) . '***';
            $emailParts[1] = substr($emailParts[1], 0, 3) . '***';
            return implode('@', $emailParts);
        } else {
            return $user->email;
        }
    }

}
