<?php

declare(strict_types=1);

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\Blog\Fillers\PostFiller\RandomImageUrlGenerator;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use Faker\Factory;

class UserFiller implements FillerInterface
{
    public function __construct(private Blog $blog) {}

    public function fill(): void
    {
        if ($this->blog->type === BlogTypeEnum::TEMP) {
            UserRepository::createGuestUser(
                $this->blog,
                'Temporary User',
                UserRoleEnum::OWNER,
                pictureUrl: RandomImageUrlGenerator::getUserImageUrl(),
            );
        } else {
            if ($this->blog->type !== BlogTypeEnum::PREVIEW) {
                // add the OWNER
                UserRepository::createUserFromHyvorUser(
                    $this->blog,
                    intval($this->blog->created_by_user_id),
                    UserRoleEnum::OWNER,
                );
            }
        }

        if (
            $this->blog->type === BlogTypeEnum::DEV ||
            $this->blog->type === BlogTypeEnum::PREVIEW
        ) {
            $faker = Factory::create();

            foreach (range(1, 5) as $i) {
                $user = UserRepository::createGuestUser($this->blog, $faker->name());
                UserRepository::updateUser($user, [
                    'picture_url' => RandomImageUrlGenerator::getUserImageUrl(),
                ]);
            }
        }
    }
}
