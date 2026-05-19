<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleBlogApiAuthorizationListener;
use App\Api\Console\Input\Blog\Webhook\CreateWebhookInput;
use App\Api\Console\Input\Blog\Webhook\GetWebhookDeliveriesInput;
use App\Api\Console\Input\Blog\Webhook\UpdateWebhookInput;
use App\Service\Limit;
use App\Service\Webhook\WebhookService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class WebhookController
{
    public function __construct(
        private ConsoleBlogApiAuthorizationListener $blogAuthListener,
        private WebhookService $webhookService,
    ) {}

    #[Route('/webhooks', methods: ['GET'])]
    public function getWebhooks(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $webhooks = $this->webhookService->getWebhooks($blog);

        return new JsonResponse(array_map(fn($w) => [
            'id' => $w->getId(),
            'url' => $w->getUrl(),
            'events' => $w->getEvents(),
            'secret' => $w->getSecret(),
        ], $webhooks));
    }

    #[Route('/webhooks', methods: ['POST'])]
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

        return new JsonResponse([
            'id' => $webhook->getId(),
            'url' => $webhook->getUrl(),
            'events' => $webhook->getEvents(),
            'secret' => $webhook->getSecret(),
        ], 201);
    }

    #[Route('/webhooks/{id}', methods: ['PATCH'])]
    public function updateWebhook(
        int $id,
        #[MapRequestPayload] UpdateWebhookInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $webhook = $this->webhookService->getWebhookByIdAndBlog($id, $blog);
        $webhook = $this->webhookService->updateWebhook($webhook, $input->url, $input->events);

        return new JsonResponse([
            'id' => $webhook->getId(),
            'url' => $webhook->getUrl(),
            'events' => $webhook->getEvents(),
            'secret' => $webhook->getSecret(),
        ]);
    }

    #[Route('/webhooks/{id}', methods: ['DELETE'])]
    public function deleteWebhook(int $id): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $webhook = $this->webhookService->getWebhookByIdAndBlog($id, $blog);
        $this->webhookService->deleteWebhook($webhook);

        return new JsonResponse();
    }

    #[Route('/webhook-deliveries', methods: ['GET'])]
    public function getDeliveries(
        #[MapQueryString] GetWebhookDeliveriesInput $input = new GetWebhookDeliveriesInput(),
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $deliveries = $this->webhookService->getWebhookDeliveries(
            $blog,
            $input->webhook_id,
            $input->limit,
            $input->offset,
        );

        return new JsonResponse(array_map(fn($d) => [
            'id' => $d->getId(),
            'url' => $d->getUrl(),
            'event' => $d->getEvent(),
            'status' => $d->getStatus(),
            'response' => $d->getResponse(),
            'created_at' => $d->getCreatedAt()?->getTimestamp(),
        ], $deliveries));
    }
}
