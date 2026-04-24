<?php

namespace App\Api\Console\Resolver;

use App\Entity\Blog;
use App\Service\Blog\BlogService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class BlogResolver implements ValueResolverInterface
{
    public function __construct(
        private BlogService $blogService
    ) {}

    /**
     * @return iterable<Blog>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $controllerName = $argument->getControllerName();
        if (!str_starts_with($controllerName, 'App\Api\Console\Controller\\')) {
            return [];
        }

        $argumentType = $argument->getType();

        if (
            !$argumentType ||
            $argumentType !== Blog::class
        ) {
            return [];
        }

        $subdomainValue = $request->attributes->get('subdomain');
        $subdomain = is_scalar($subdomainValue) ? strval($subdomainValue) : null;

        if (!$subdomain) {
            throw new BadRequestException('Missing subdomain');
        }

        $blog = $this->blogService->getBlogBySubdomain($subdomain);

        if (!$blog) {
            throw new BadRequestException('Blog not found');
        }

        return [$blog];
    }
}