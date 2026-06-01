<?php

namespace App\Service\Webhook;

use App\Entity\Blog;
use App\Entity\Enum\WebhookEvent;
use App\Entity\Webhook;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class WebhookService
{
    use ClockAwareTrait;

    public function __construct(private EntityManagerInterface $em) {}

    /** @return Webhook[] */
    public function getWebhooks(Blog $blog): array
    {
        return $this->em->getRepository(Webhook::class)->findBy(
            ['blog_id' => $blog->getId()],
        );
    }

    public function getWebhooksCount(Blog $blog): int
    {
        return $this->em->getRepository(Webhook::class)->count(['blog_id' => $blog->getId()]);
    }

    /** @param WebhookEvent[] $events */
    public function createWebhook(Blog $blog, string $url, array $events): Webhook
    {
        $webhook = new Webhook();
        $webhook->setBlog($blog);
        $webhook->setBlogId($blog->getId());
        $webhook->setUrl($url);
        $webhook->setEvents($events);
        $webhook->setSecret(bin2hex(random_bytes(16)));
        $now = $this->now();
        $webhook->setCreatedAt($now);
        $webhook->setUpdatedAt($now);

        $this->em->persist($webhook);
        $this->em->flush();
        return $webhook;
    }

    /** @param WebhookEvent[]|null $events */
    public function updateWebhook(Webhook $webhook, ?string $url, ?array $events): Webhook
    {
        if ($url !== null) {
            $webhook->setUrl($url);
        }
        if ($events !== null) {
            $webhook->setEvents($events);
        }
        $webhook->setUpdatedAt($this->now());
        $this->em->flush();
        return $webhook;
    }

    public function deleteWebhook(Webhook $webhook): void
    {
        $this->em->remove($webhook);
        $this->em->flush();
    }
}
