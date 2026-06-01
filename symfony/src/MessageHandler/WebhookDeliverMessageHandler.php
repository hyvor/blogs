<?php

namespace App\MessageHandler;

use App\Entity\Enum\WebhookDeliveryStatus;
use App\Entity\WebhookDelivery;
use App\Message\WebhookDeliverMessage;
use App\Service\Webhook\WebhookDeliveryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class WebhookDeliverMessageHandler
{

    public const RETRIES = [
        // 1 minute
        60,
        // 5 minutes
        60 * 5,
        // 30 minutes
        60 * 30,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private WebhookDeliveryService $deliveryService,
    ) {}

    public function __invoke(WebhookDeliverMessage $message): void
    {
        $delivery = $this->em->find(WebhookDelivery::class, $message->deliveryId);

        if ($delivery === null) {
            throw new UnrecoverableMessageHandlingException("WebhookDelivery {$message->deliveryId} not found");
        }

        $this->deliveryService->deliver($delivery);

        if ($delivery->getStatus() === WebhookDeliveryStatus::FAILED) {
            // failed after all retries, do not retry again
            throw new UnrecoverableMessageHandlingException("WebhookDelivery {$message->deliveryId} failed after retries");
        } elseif ($delivery->getStatus() === WebhookDeliveryStatus::RETRYING) {
            // try_count is 1-indexed here (already incremented by deliver()); use try_count-1 as RETRIES index
            $retryIndex = min($delivery->getTryCount() - 1, count(self::RETRIES) - 1);
            throw new RecoverableMessageHandlingException(
                "WebhookDelivery {$message->deliveryId} will be retried",
                retryDelay: self::RETRIES[$retryIndex] * 1000,
            );
        }
    }
}
