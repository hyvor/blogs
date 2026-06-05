<?php

namespace App\Api\Data\Controller;

use App\Api\Data\DataApiHelper;
use App\Api\Data\Factory\BlogObjectFactory;
use App\Api\Data\KeysFilter;
use App\Api\Data\Resolver\MapBlogFromSubdomain;
use App\Entity\Blog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BlogController
{
    public function __construct(
        private DataApiHelper $dataApiHelper,
        private BlogObjectFactory $blogObjectFactory,
    ) {}

    #[Route('/blog', name: 'blog', methods: ['GET'])]
    public function getBlog(#[MapBlogFromSubdomain] Blog $blog, Request $request): JsonResponse
    {
        $languageCode = $request->query->get('language');
        $language = $this->dataApiHelper->getLanguage($blog, is_string($languageCode) ? $languageCode : null);

        $blogObject = $this->blogObjectFactory->create($blog, $language);

        $keys = $request->query->get('keys');
        $filtered = KeysFilter::filter($blogObject, is_string($keys) ? $keys : null);

        return new JsonResponse($filtered);
    }
}
