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
            // will be retried
            throw new RecoverableMessageHandlingException(
                "WebhookDelivery {$message->deliveryId} will be retried",
                // TODO: this must be based on try_count and RETRIES
                retryDelay: 60 * 1000,
            );
        }
    }
}
