<?php

namespace App\Service\Webhook;

use App\Entity\Blog;
use App\Entity\Enum\WebhookDeliveryStatus;
use App\Entity\Enum\WebhookEvent;
use App\Entity\Webhook;
use App\Entity\WebhookDelivery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookDeliveryService
{
    use ClockAwareTrait;

    private const MAX_RETRIES = 3;

    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $httpClient,
    ) {}

    /**
     * @return WebhookDelivery[]
     */
    public function getWebhookDeliveries(Blog $blog, ?int $webhookId, int $limit, int $offset): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('d')
            ->from(WebhookDelivery::class, 'd')
            ->join('d.webhook', 'w')
            ->where('w.blog = :blogId')
            ->setParameter('blogId', $blog->getId())
            ->orderBy('d.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($webhookId !== null) {
            $qb->andWhere('d.webhook = :webhookId')
                ->setParameter('webhookId', $webhookId);
        }

        /** @var WebhookDelivery[] $result */
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /** @param array<string, mixed> $data */
    public function createDelivery(Webhook $webhook, WebhookEvent $event, array $data): WebhookDelivery
    {
        $now = $this->now();
        $delivery = new WebhookDelivery();
        $delivery->setWebhook($webhook);
        $delivery->setUrl($webhook->getUrl());
        $delivery->setEvent($event);
        $delivery->setData($data);
        $delivery->setStatus(WebhookDeliveryStatus::PENDING);
        $delivery->setCreatedAt($now);
        $delivery->setUpdatedAt($now);

        $this->em->persist($delivery);
        $this->em->flush();

        return $delivery;
    }

    public function deliver(WebhookDelivery $delivery): void
    {
        $webhook = $delivery->getWebhook();
        $blog = $webhook->getBlog();

        $payload = [
            'subdomain' => $blog->getSubdomain(),
            'timestamp' => $delivery->getCreatedAt()?->getTimestamp(),
            'event' => $delivery->getEvent()->value,
            'data' => $delivery->getData(),
        ];

        $signature = hash_hmac('sha256', (string)json_encode($payload), $webhook->getSecret());

        try {
            $response = $this->httpClient->request('POST', $delivery->getUrl(), [
                'headers' => ['X-Signature' => $signature],
                'json' => $payload,
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $body = substr($response->getContent(false), 0, 1024);

            $delivery->setHttpStatus($statusCode);
            $delivery->setResponse($body);

            if ($statusCode >= 200 && $statusCode < 300) {
                $delivery->setStatus(WebhookDeliveryStatus::SUCCESS);
            } else {
                $this->handleDeliveryFailure($delivery);
            }

            $delivery->setUpdatedAt($this->now());
            $this->em->flush();
        } catch (TransportExceptionInterface $e) {
            $delivery->setResponse(substr($e->getMessage(), 0, 1024));
            $this->handleDeliveryFailure($delivery);
        }

        $this->em->flush();
    }

    private function handleDeliveryFailure(WebhookDelivery $delivery): void
    {
        $delivery->setTryCount($delivery->getTryCount() + 1);
        $delivery->setLastTryAt($this->now());
        $delivery->setUpdatedAt($this->now());

        if ($delivery->getTryCount() < self::MAX_RETRIES) {
            $delivery->setStatus(WebhookDeliveryStatus::RETRYING);
        } else {
            $delivery->setStatus(WebhookDeliveryStatus::FAILED);
        }
    }
}
