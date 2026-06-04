<?php

namespace App\Api\Data\Controller;

use App\Api\Data\DataApiHelper;
use App\Api\Data\Factory\BlogObjectFactory;
use App\Api\Data\KeysFilter;
use App\Entity\Language;
use App\Entity\Navigation;
use App\Service\Blog\BlogService;
use App\Service\Language\LanguageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BlogController
{
    public function __construct(
        private BlogService $blogService,
        private DataApiHelper $dataApiHelper,
        private BlogObjectFactory $blogObjectFactory,
        private LanguageService $languageService,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/blog', name: 'blog', methods: ['GET'])]
    public function getBlog(string $subdomain, Request $request): JsonResponse
    {
        $blog = $this->blogService->getBlogBySubdomain($subdomain);
        if ($blog === null) {
            return new JsonResponse(['message' => 'Blog not found'], 404);
        }

        $languageCode = $request->query->get('language');
        $language = $this->dataApiHelper->getLanguage($blog, is_string($languageCode) ? $languageCode : null);

        $navigations = $this->em->getRepository(Navigation::class)->findBy(['blog' => $blog]);
        $allLanguages = $this->languageService->getAllLanguages($blog);

        $blogObject = $this->blogObjectFactory->create($blog, $language, $navigations, $allLanguages);

        $keys = $request->query->get('keys');
        $filtered = KeysFilter::filter($blogObject, is_string($keys) ? $keys : null);

        return new JsonResponse($filtered);
    }
}
