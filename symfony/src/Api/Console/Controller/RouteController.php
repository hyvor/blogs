<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Route\CreateRouteInput;
use App\Api\Console\Input\Route\UpdateRouteInput;
use App\Api\Console\Object\RouteObject;
use App\Entity\Route as RouteEntity;
use App\Service\Limit;
use App\Service\Route\RouteService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[ScopeRequired(Scope::ROUTES_READ)]
    #[OA\Get(
        description: 'Returns the custom routes of the blog.',
        summary: 'List routes',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of routes.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: RouteObject::class))),
    )]
    public function list(): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $routes = $this->routeService->getRoutes($blog);

        return new JsonResponse(array_map(fn($r) => new RouteObject($r), $routes));
    }

    #[Route('/route', methods: ['POST'])]
    #[ScopeRequired(Scope::ROUTES_WRITE)]
    #[OA\Post(
        description: 'Creates a custom route that maps a path to a template.',
        summary: 'Create a route',
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the created route.',
        content: new Model(type: RouteObject::class),
    )]
    public function create(
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
    #[ScopeRequired(Scope::ROUTES_WRITE)]
    #[OA\Patch(
        description: 'Updates a custom route.',
        summary: 'Update a route',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated route.',
        content: new Model(type: RouteObject::class),
    )]
    public function update(
        #[MapBlogEntity] RouteEntity $route,
        #[MapRequestPayload] UpdateRouteInput $input,
    ): JsonResponse {
        $route = $this->routeService->updateRoute($route, (array) $input);

        return new JsonResponse(new RouteObject($route));
    }

    #[Route('/route/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::ROUTES_WRITE)]
    #[OA\Delete(
        description: 'Deletes a custom route.',
        summary: 'Delete a route',
    )]
    #[OA\Response(response: 200, description: 'The route was deleted.')]
    public function delete(#[MapBlogEntity] RouteEntity $route): JsonResponse
    {
        $this->routeService->deleteRoute($route);

        return new JsonResponse();
    }
}
