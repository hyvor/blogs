<?php

namespace App\Service\Post\MessageHandler;

use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use App\Service\Post\Event\PostVariantPublishedEvent;
use App\Service\Post\Message\PublishScheduledPostVariantsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PublishScheduledPostVariantsMessageHandler
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $ed,
    ) {}

    public function __invoke(PublishScheduledPostVariantsMessage $message): void
    {
        foreach ($this->getDueScheduledVariants() as $variant) {
            $variant->setStatus(PostVariantStatus::PUBLISHED);
            $variant->setUpdatedAt($this->now());
            $this->em->flush();

            $this->ed->dispatch(new PostVariantPublishedEvent($variant));
        }
    }

    /**
     * @return PostVariant[]
     */
    private function getDueScheduledVariants(): array
    {
        /** @var PostVariant[] */
        return $this->em->getRepository(PostVariant::class)->createQueryBuilder('v')
            ->where('v.status = :status')
            ->andWhere('v.published_at <= :now')
            ->setParameter('status', PostVariantStatus::SCHEDULED)
            ->setParameter('now', $this->now())
            ->orderBy('v.id')
            ->getQuery()
            ->getResult();
    }
}
