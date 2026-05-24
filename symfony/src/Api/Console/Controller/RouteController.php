<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Input\Blog\Route\CreateRouteInput;
use App\Api\Console\Input\Blog\Route\UpdateRouteInput;
use App\Api\Console\Object\RouteObject;
use App\Service\Limit;
use App\Service\Route\RouteService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class RouteController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private RouteService $routeService,
    ) {}

    #[Route('/routes', methods: ['GET'])]
    public function getRoutes(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $routes = $this->routeService->getRoutes($blog);

        return new JsonResponse(array_map(fn($r) => new RouteObject($r), $routes));
    }

    #[Route('/route', methods: ['POST'])]
    public function createRoute(
        #[MapRequestPayload] CreateRouteInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->routeService->getRoutesCount($blog) >= Limit::MAX_ROUTES_PER_BLOG) {
            throw new UnprocessableEntityHttpException(
                'You have reached the maximum number of routes (' . Limit::MAX_ROUTES_PER_BLOG . ')'
            );
        }

        $route = $this->routeService->createRoute(
            $blog,
            $input->name,
            $input->match,
            $input->template,
            $input->posts_filter,
            $input->content_type,
        );

        return new JsonResponse(new RouteObject($route), 201);
    }

    #[Route('/route/{id}', methods: ['PATCH'])]
    public function updateRoute(
        int $id,
        #[MapRequestPayload] UpdateRouteInput $input,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $route = $this->routeService->getRouteByIdAndBlog($id, $blog);
        $route = $this->routeService->updateRoute(
            $route,
            $input->name,
            $input->match,
            $input->template,
            $input->posts_filter,
            $input->content_type,
        );

        return new JsonResponse(new RouteObject($route));
    }

    #[Route('/route/{id}', methods: ['DELETE'])]
    public function deleteRoute(int $id): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $route = $this->routeService->getRouteByIdAndBlog($id, $blog);
        $this->routeService->deleteRoute($route);

        return new JsonResponse();
    }
}
