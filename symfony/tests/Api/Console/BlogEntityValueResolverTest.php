<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Resolver\BlogEntityValueResolver;
use App\Entity\ApiKey;
use App\Entity\Blog;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(BlogEntityValueResolver::class)]
class BlogEntityValueResolverTest extends KernelTestCase
{

    /** @return array<mixed> */
    private function resolve(Request $request, ArgumentMetadata $argument): array
    {
        $resolver = self::getContainer()->get(BlogEntityValueResolver::class);
        assert($resolver instanceof BlogEntityValueResolver);
        return iterator_to_array($resolver->resolve($request, $argument));
    }

    public function test_ignores_without_attribute(): void
    {
        $request = Request::create('/navigation/1', 'DELETE');
        $argument = new ArgumentMetadata('arg', null, false, false, null, attributes: []);

        $result = $this->resolve($request, $argument);
        $this->assertEmpty($result);
    }

    public function test_ignores_without_class(): void
    {
        $request = Request::create('/navigation/1', 'DELETE');
        $argument = new ArgumentMetadata('arg', null, false, false, null, attributes: [new MapBlogEntity()]);

        $result = $this->resolve($request, $argument);
        $this->assertEmpty($result);
    }

    public function test_when_entity_not_found(): void
    {
        $authorizationListener = $this->createStub(ConsoleApiAuthorizationListener::class);
        $authorizationListener->method('getBlog')->willReturn((new Blog())->setId(1));
        $this->getContainer()->set(ConsoleApiAuthorizationListener::class, $authorizationListener);

        $request = Request::create('/navigation/99999', 'DELETE');
        $request->attributes->set('id', 99999);
        $argument = new ArgumentMetadata('arg', ApiKey::class, false, false, null, attributes: [new MapBlogEntity()]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Entity not found');

        $this->resolve($request, $argument);
    }

    public function test_when_entity_does_not_belong_to_blog(): void
    {
        $blog = BlogFactory::createOne();

        $authorizationListener = $this->createStub(ConsoleApiAuthorizationListener::class);
        $authorizationListener->method('getBlog')->willReturn((new Blog())->setId($blog->getId() + 1));
        $this->getContainer()->set(ConsoleApiAuthorizationListener::class, $authorizationListener);

        $apiKey = ApiKeyFactory::createOne([
            'blog' => $blog,
        ]);

        $request = Request::create('/navigation/1', 'DELETE');
        $request->attributes->set('id', $apiKey->getId());
        $argument = new ArgumentMetadata('arg', ApiKey::class, false, false, null, attributes: [new MapBlogEntity()]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Entity does not belong to blog');

        $this->resolve($request, $argument);
    }

    public function test_resolves_entity(): void
    {
        $blog = BlogFactory::createOne();
        $authorizationListener = $this->createStub(ConsoleApiAuthorizationListener::class);
        $authorizationListener->method('getBlog')->willReturn($blog);
        $this->getContainer()->set(ConsoleApiAuthorizationListener::class, $authorizationListener);

        $apiKey = ApiKeyFactory::createOne([
            'blog' => $blog,
        ]);

        $request = Request::create('/navigation/1', 'DELETE');
        $request->attributes->set('id', $apiKey->getId());
        $argument = new ArgumentMetadata('arg', ApiKey::class, false, false, null, attributes: [new MapBlogEntity()]);

        $result = $this->resolve($request, $argument);
        $this->assertCount(1, $result);
        $resolved = $result[0];
        assert($resolved instanceof ApiKey);
        $this->assertSame($apiKey->getId(), $resolved->getId());
    }
}
