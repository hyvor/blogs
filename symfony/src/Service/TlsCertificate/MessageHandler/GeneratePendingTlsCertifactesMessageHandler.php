<?php

namespace App\Service\TlsCertificate\MessageHandler;

use App\Entity\Blog;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\TlsCertificateStatus;
use App\Entity\TlsCertificate;
use App\Service\TlsCertificate\Message\GeneratePendingTlsCertificatesMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GeneratePendingTlsCertifactesMessageHandler
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
    )
    {
    }

    public function __invoke(GeneratePendingTlsCertificatesMessage $message): void
    {
        $now = new \DateTimeImmutable();

        // Get blogs with hosting_at = DOMAIN, left joined with their TLS certificate
        /** @var array{0: Blog, 1: TlsCertificate|null}[] $results */
        $results = $this->em->createQueryBuilder()
            ->select('b', 'tc')
            ->from(Blog::class, 'b')
            ->leftJoin(TlsCertificate::class, 'tc', 'WITH', 'tc.blog_id = b.id')
            ->where('b.hosting_at = :hosting_at')
            ->andWhere('tc.id IS NULL OR tc.status = :pending_status')
            ->setParameter('hosting_at', BlogHostingAt::DOMAIN)
            ->setParameter('active_status', TlsCertificateStatus::PENDING)
            ->getQuery()
            ->getResult();

        foreach ($results as [$blog, $tlsCertificate]) {

            if ($tlsCertificate === null) {
                $this->createTlsCertificate($blog);
            }

            $this->generateTlsCertificate($tlsCertificate);
        }
    }

    private function createTlsCertificate(Blog $blog): TlsCertificate
    {
        $tlsCertificate = new TlsCertificate();
        $tlsCertificate->setBlogId($blog->getId());
        $tlsCertificate->setCreatedAt($this->now());
        $tlsCertificate->setUpdatedAt($this->now());
        $tlsCertificate->setStatus(TlsCertificateStatus::PENDING);

        $this->em->persist($tlsCertificate);
        $this->em->flush();

        return $tlsCertificate;
    }

    private function generateTlsCertificate(TlsCertificate $tlsCertificate): void
    {
        // TODO: implement certificate generation
    }
}
