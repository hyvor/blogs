<?php

namespace App\Service\Blog\Seeder;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class TagSeeder
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Creates the default "welcome" tag, plus 5 random tags for DEV/PREVIEW blogs.
     * Returns the primary "welcome" tag.
     */
    public function seed(Blog $blog): Tag
    {
        $welcome = $this->create($blog, 'welcome');

        if ($blog->getType() === BlogType::DEV || $blog->getType() === BlogType::PREVIEW) {
            $faker = Factory::create();
            for ($i = 0; $i < 5; $i++) {
                $this->create($blog, strtolower($faker->word()));
            }
        }

        $this->em->flush();

        return $welcome;
    }

    private function create(Blog $blog, string $slug): Tag
    {
        $tag = new Tag();
        $tag->setBlog($blog);
        $tag->setBlogId($blog->getId());
        $tag->setSlug($slug);
        $this->em->persist($tag);
        return $tag;
    }
}
