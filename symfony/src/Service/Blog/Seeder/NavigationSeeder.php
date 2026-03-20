<?php

namespace App\Service\Blog\Seeder;

use App\Entity\Blog;
use App\Entity\Enum\BlogType;
use App\Entity\Navigation;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class NavigationSeeder
{
    use ClockAwareTrait;

    public function __construct(private EntityManagerInterface $em) {}

    public function seed(Blog $blog): void
    {
        $navs = [
            ['type' => 'header', 'url' => '/about'],
            ['type' => 'header', 'url' => '/contact'],
            ['type' => 'footer', 'url' => '/privacy'],
        ];

        if ($blog->getType() === BlogType::DEV || $blog->getType() === BlogType::PREVIEW) {
            $navs[0]['type'] = 'footer';
            $navs[1]['type'] = 'footer';

            $navs[] = ['type' => 'header', 'url' => '/content-style'];

            $user = $this->em->getRepository(User::class)->findOneBy(['blog' => $blog]);
            if ($user) {
                $navs[] = ['type' => 'header', 'url' => '/author/' . $user->getSlug()];
            }

            $tag = $this->em->getRepository(Tag::class)->findOneBy(['blog' => $blog]);
            if ($tag) {
                $navs[] = ['type' => 'header', 'url' => '/tag/' . $tag->getSlug()];
            }
        }

        $now = $this->now();
        foreach ($navs as $i => $def) {
            $nav = new Navigation();
            $nav->setBlog($blog);
            $nav->setBlogId($blog->getId());
            $nav->setUrl($def['url']);
            $nav->setType($def['type']);
            $nav->setSort($i);
            $nav->setCreatedAt($now);
            $nav->setUpdatedAt($now);
            $this->em->persist($nav);
        }

        $this->em->flush();
    }
}
