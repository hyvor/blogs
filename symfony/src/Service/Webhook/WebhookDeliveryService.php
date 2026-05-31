<?php

namespace App\Service\Webhook;

use App\Entity\Blog;
use App\Entity\WebhookDelivery;
use Doctrine\ORM\EntityManagerInterface;

class WebhookDeliveryService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @return WebhookDelivery[]
     */
    public function getWebhookDeliveries(Blog $blog, ?int $webhookId, int $limit, int $offset): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('d')
            ->from(WebhookDelivery::class, 'd')
            ->join('d.webhook', 'w')
            ->where('w.blog_id = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('d.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($webhookId !== null) {
            $qb->andWhere('d.webhook_id = :webhookId')
                ->setParameter('webhookId', $webhookId);
        }

        /** @var WebhookDelivery[] $result */
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}
