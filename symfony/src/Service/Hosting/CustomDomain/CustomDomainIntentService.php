<?php

namespace App\Service\Hosting\CustomDomain;

use App\Entity\Blog;
use App\Entity\CustomDomainIntent;
use App\Entity\Enum\CustomDomainTlsProvider;
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

    /**
     * Creates or upserts the blog's pending custom domain intent. Always resets any TLS
     * material from a previous attempt - an intent represents starting fresh for whatever
     * domain/provider was just requested, so a stale cert from a different provider/domain
     * can never survive a re-POST.
     */
    public function createIntent(
        Blog $blog,
        string $domain,
        CustomDomainTlsProvider $tlsProvider = CustomDomainTlsProvider::AUTO
    ): CustomDomainIntent
    {
        $intent = $this->getBlogCustomDomainIntent($blog);

        if ($intent === null) {
            $intent = new CustomDomainIntent();
            $intent->setBlog($blog);
            $intent->setCreatedAt($this->now());
        }

        $intent->setDomain($domain);
        $intent->setTlsProvider($tlsProvider);
        $intent->setPrivateKeyEncrypted(null);
        $intent->setCertificate(null);
        $intent->setValidFrom(null);
        $intent->setValidTo(null);
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
