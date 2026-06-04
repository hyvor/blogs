<?php

namespace App\Api\BlogDelivery;

use App\Entity\Blog;
use App\Repository\BlogRepository;
use App\Service\Delivery\DeliveryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogDeliveryController extends AbstractController
{
    public function __construct(
        private DeliveryService $deliveryService,
        private BlogRepository $blogRepository,
    ) {}

    #[Route('/blog/{subdomain}/{path}', defaults: ['path' => null], requirements: ['path' => '.*'], methods: ['GET'])]
    public function deliver(string $subdomain, ?string $path): Response
    {
        $blog = $this->blogRepository->findBySubdomain($subdomain);
        if ($blog === null) {
            return new Response('Blog not found', 404);
        }

        $path = '/' . ltrim($path ?? '', '/');

        return $this->deliveryService->getSymfonyResponse($blog, $path);
    }
}
