<?php

namespace App\Api\Data\Resolver;

use App\Service\Blog\BlogService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogValueResolver implements ValueResolverInterface
{
    public function __construct(private BlogService $blogService) {}

    /** @return iterable<mixed> */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (count($argument->getAttributes(MapBlogFromSubdomain::class)) === 0) {
            return [];
        }

        $subdomain = $request->attributes->get('subdomain');
        if (!is_string($subdomain)) {
            return [];
        }

        $blog = $this->blogService->getBlogBySubdomain($subdomain);
        if ($blog === null) {
            throw new NotFoundHttpException('Blog not found');
        }

        yield $blog;
    }
}
