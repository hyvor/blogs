<?php

namespace App\Api\Data\Controller;

use App\Api\Data\DataApiHelper;
use App\Api\Data\Factory\BlogObjectFactory;
use App\Api\Data\Input\GetBlogInput;
use App\Api\Data\KeysFilter;
use App\Api\Data\Resolver\MapBlogFromSubdomain;
use App\Entity\Blog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class BlogController
{
    public function __construct(
        private DataApiHelper $dataApiHelper,
        private BlogObjectFactory $blogObjectFactory,
    ) {}

    #[Route('/blog', name: 'blog', methods: ['GET'])]
    public function getBlog(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetBlogInput $input): JsonResponse
    {
        $language = $this->dataApiHelper->getLanguage($blog, $input->language);
        $blogObject = $this->blogObjectFactory->create($blog, $language);
        $filtered = KeysFilter::filter($blogObject, $input->keys);

        return new JsonResponse($filtered);
    }
}
