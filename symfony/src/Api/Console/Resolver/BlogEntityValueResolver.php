<?php

namespace App\Api\Console\Resolver;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Entity\Blog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogEntityValueResolver implements ValueResolverInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (count($argument->getAttributes(MapBlogEntity::class)) === 0) {
            return [];
        }

        $class = $argument->getType();
        if ($class === null) {
            return [];
        }

        $id = $request->attributes->get('id');
        $blog = $request->attributes->get(ConsoleApiAuthorizationListener::RESOLVED_BLOG_KEY);

        assert($blog instanceof Blog);

        $entity = $this->em->find($class, $id);

        if ($entity === null) {
            throw new NotFoundHttpException('Resource not found');
        }

        if ($entity->getBlogId() !== $blog->getId()) {
            throw new NotFoundHttpException('Resource not found');
        }

        yield $entity;
    }
}
