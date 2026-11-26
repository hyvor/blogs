<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Webhook\CreateWebhookInput;
use App\Api\Console\Input\Webhook\GetWebhookDeliveriesInput;
use App\Api\Console\Input\Webhook\UpdateWebhookInput;
use App\Api\Console\Object\WebhookDeliveryObject;
use App\Api\Console\Object\WebhookObject;
use App\Entity\Webhook;
use App\Service\Limit;
use App\Service\Webhook\WebhookDeliveryService;
use App\Service\Webhook\WebhookService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[OA\Get(
        description: 'Returns the webhooks of the blog.',
        summary: 'List webhooks',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of webhooks.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: WebhookObject::class))),
    )]
    public function list(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $webhooks = $this->webhookService->getWebhooks($blog);

        return new JsonResponse(array_map(fn($w) => new WebhookObject($w), $webhooks));
    }

    #[Route('/webhook', methods: ['POST'])]
    #[ScopeRequired(Scope::WEBHOOKS_WRITE)]
    #[OA\Post(
        description: 'Creates a webhook that is called when the selected events occur.',
        summary: 'Create a webhook',
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the created webhook.',
        content: new Model(type: WebhookObject::class),
    )]
    public function create(
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
    #[OA\Patch(
        description: 'Updates a webhook.',
        summary: 'Update a webhook',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated webhook.',
        content: new Model(type: WebhookObject::class),
    )]
    public function update(
        #[MapBlogEntity] Webhook $webhook,
        #[MapRequestPayload] UpdateWebhookInput $input,
    ): JsonResponse {
        $webhook = $this->webhookService->updateWebhook($webhook, $input->url, $input->events);

        return new JsonResponse(new WebhookObject($webhook));
    }

    #[Route('/webhook/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::WEBHOOKS_WRITE)]
    #[OA\Delete(
        description: 'Deletes a webhook.',
        summary: 'Delete a webhook',
    )]
    #[OA\Response(response: 200, description: 'The webhook was deleted.')]
    public function delete(#[MapBlogEntity] Webhook $webhook): JsonResponse
    {
        $this->webhookService->deleteWebhook($webhook);

        return new JsonResponse();
    }

    #[Route('/webhook-deliveries', methods: ['GET'])]
    #[ScopeRequired(Scope::WEBHOOKS_READ)]
    #[OA\Get(
        description: 'Returns the recent webhook deliveries of the blog.',
        summary: 'List webhook deliveries',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of deliveries.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: WebhookDeliveryObject::class))),
    )]
    public function listDeliveries(
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
