<?php

namespace App\Service\Hosting\CustomDomain;

use App\Entity\Blog;
use App\Entity\CustomDomainIntent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class CustomDomainIntentService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function getCustomDomainIntent(string $domain): ?CustomDomainIntent
    {
        return $this->em->getRepository(CustomDomainIntent::class)->findOneBy(['domain' => $domain]);
    }

    public function getBlogCustomDomainIntent(Blog $blog): ?CustomDomainIntent
    {
        return $this->em->getRepository(CustomDomainIntent::class)->findOneBy(['blog' => $blog]);
    }

    public function createIntent(Blog $blog, string $domain): CustomDomainIntent
    {
        $intent = $this->getBlogCustomDomainIntent($blog);

        if ($intent === null) {
            $intent = new CustomDomainIntent();
            $intent->setBlog($blog);
            $intent->setCreatedAt($this->now());
        }

        $intent->setDomain($domain);
        $intent->setUpdatedAt($this->now());

        $this->em->persist($intent);
        $this->em->flush();

        return $intent;
    }

    public function deleteIntent(CustomDomainIntent $intent, bool $flush = true): void
    {
        $this->em->remove($intent);
        if ($flush) {
            $this->em->flush();
        }
    }

}
