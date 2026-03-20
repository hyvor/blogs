<?php

namespace App\Service\Blog\Seeder;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class UserSeeder
{
    private const IMAGE_URL = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images';

    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Creates the blog owner and optional guest users.
     * Returns the owner User, or null for PREVIEW blogs (which have no owner).
     */
    public function seed(Blog $blog): ?User
    {
        $owner = null;

        if ($blog->getType() === BlogType::TEMP) {
            $owner = $this->createUser($blog, null, UserRole::OWNER, $this->randomUserImageUrl());
        } elseif ($blog->getType() !== BlogType::PREVIEW) {
            $owner = $this->createUser($blog, $blog->getHyvorUserId(), UserRole::OWNER);
        }

        if ($blog->getType() === BlogType::DEV || $blog->getType() === BlogType::PREVIEW) {
            $faker = Factory::create();
            for ($i = 0; $i < 5; $i++) {
                $this->createUser($blog, null, UserRole::EDITOR, $this->randomUserImageUrl());
            }
        }

        $this->em->flush();

        return $owner;
    }

    private function createUser(Blog $blog, ?int $hyvorUserId, UserRole $role, ?string $pictureUrl = null): User
    {
        $user = new User();
        $user->setBlog($blog);
        $user->setBlogId($blog->getId());
        $user->setHyvorUserId($hyvorUserId);
        $user->setRole($role);
        $user->setStatus('active');
        $user->setSlug(bin2hex(random_bytes(8)));
        $user->setPictureUrl($pictureUrl);
        $this->em->persist($user);
        return $user;
    }

    private function randomUserImageUrl(): string
    {
        return self::IMAGE_URL . '/author-images/' . rand(1, 5) . '.webp';
    }
}
