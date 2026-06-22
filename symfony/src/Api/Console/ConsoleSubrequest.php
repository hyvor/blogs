<?php

namespace App\Api\Console;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Entity\Blog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ConsoleSubrequest
{

    public function __construct(
        private HttpKernelInterface $kernel,
        private ConsoleApiAuthorizationListener $consoleApiAuthorizationListener,
    ) {}

    public function callBlogEndpoint(
        Blog $blog,
        string $method,
        string $endpoint, // e.g. /blog
    ): JsonResponse
    {
        $path = '/api/console/v0/blog/' . $blog->getSubdomain() . $endpoint;
        $request = Request::create($path, $method);
        $this->consoleApiAuthorizationListener->setBlogToRequest($request, $blog);

        $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST);

        if ($response->getStatusCode() !== 200) {
            throw new HttpException($response->getStatusCode(), 'Subrequest to ' . $path . ' failed with status code ' . $response->getStatusCode());
        }

        return $response;
    }

}