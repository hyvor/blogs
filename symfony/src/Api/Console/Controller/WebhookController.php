<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Blog\Webhook\CreateWebhookInput;
use App\Api\Console\Input\Blog\Webhook\GetWebhookDeliveriesInput;
use App\Api\Console\Input\Blog\Webhook\UpdateWebhookInput;
use App\Api\Console\Object\WebhookDeliveryObject;
use App\Api\Console\Object\WebhookObject;
use App\Entity\Webhook;
use App\Service\Limit;
use App\Service\Webhook\WebhookDeliveryService;
use App\Service\Webhook\WebhookService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class WebhookController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private WebhookService $webhookService,
        private WebhookDeliveryService $webhookDeliveryService,
    ) {}

    #[Route('/webhooks', methods: ['GET'])]
    #[ScopeRequired(Scope::WEBHOOKS_READ)]
    public function getWebhooks(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $webhooks = $this->webhookService->getWebhooks($blog);

        return new JsonResponse(array_map(fn($w) => new WebhookObject($w), $webhooks));
    }

    #[Route('/webhook', methods: ['POST'])]
    #[ScopeRequired(Scope::WEBHOOKS_WRITE)]
    public function createWebhook(
        #[MapRequestPayload] CreateWebhookInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->webhookService->getWebhooksCount($blog) >= Limit::MAX_WEBHOOKS_PER_BLOG) {
            throw new UnprocessableEntityHttpException(
                'You have reached the maximum number of webhooks (' . Limit::MAX_WEBHOOKS_PER_BLOG . ')'
            );
        }

        $webhook = $this->webhookService->createWebhook($blog, $input->url, $input->events);

        return new JsonResponse(new WebhookObject($webhook), 201);
    }

    #[Route('/webhook/{id}', methods: ['PATCH'])]
    #[ScopeRequired(Scope::WEBHOOKS_WRITE)]
    public function updateWebhook(
        #[MapBlogEntity] Webhook $webhook,
        #[MapRequestPayload] UpdateWebhookInput $input,
    ): JsonResponse {
        $webhook = $this->webhookService->updateWebhook($webhook, $input->url, $input->events);

        return new JsonResponse(new WebhookObject($webhook));
    }

    #[Route('/webhook/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::WEBHOOKS_WRITE)]
    public function deleteWebhook(#[MapBlogEntity] Webhook $webhook): JsonResponse
    {
        $this->webhookService->deleteWebhook($webhook);

        return new JsonResponse();
    }

    #[Route('/webhook-deliveries', methods: ['GET'])]
    #[ScopeRequired(Scope::WEBHOOKS_READ)]
    public function getDeliveries(
        #[MapQueryString] GetWebhookDeliveriesInput $input = new GetWebhookDeliveriesInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $deliveries = $this->webhookDeliveryService->getWebhookDeliveries(
            $blog,
            $input->webhook_id,
            $input->limit,
            $input->offset,
        );

        return new JsonResponse(array_map(fn($d) => new WebhookDeliveryObject($d), $deliveries));
    }
}
