<?php

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;
use App\Domains\User\UserRepository;
use App\Models\Blog;
use Faker\Factory;

class UserFiller implements FillerInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function fill()
    {
        if ($this->blog->type !== BlogTypeEnum::PREVIEW) {

            // add the OWNER
            UserRepository::createUserFromHyvorUser(
                $this->blog,
                $this->blog->hyvor_user_id,
                UserRoleEnum::OWNER,
                UserStatusEnum::ACTIVE
            );
        }

        if (
            $this->blog->type === BlogTypeEnum::DEV ||
            $this->blog->type === BlogTypeEnum::PREVIEW
        ) {
            $faker = Factory::create();

            foreach (range(1, 5) as $i) {
                UserRepository::createGuestUser($this->blog, $faker->name());
            }
        }
    }
}
