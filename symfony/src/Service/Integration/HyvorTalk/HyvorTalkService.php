<?php

namespace App\Service\Integration\HyvorTalk;

use App\Entity\Blog;
use App\Entity\HyvorTalkWebsite;
use Doctrine\ORM\EntityManagerInterface;

class HyvorTalkService
{

    public function __construct(private EntityManagerInterface $em) {}

    public function getHyvorTalkWebsiteOfBlog(Blog $blog): ?HyvorTalkWebsite
    {
        return $this->em->getRepository(HyvorTalkWebsite::class)->findOneBy(['blog' => $blog]);
    }

}
