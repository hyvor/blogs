<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\UrlData\GetUrlDataInput;
use App\Service\UrlData\UrlDataService;
use Hyvor\Unfold\Exception\UnfoldException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class UrlDataController
{
    public function __construct(private UrlDataService $urlDataService)
    {
    }

    #[Route('/url-data', methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function getData(
        #[MapQueryString] GetUrlDataInput $input,
    ): JsonResponse {
        try {
            if ($input->type === 'link') {
                $data = $this->urlDataService->getLink($input->url);
            } else {
                $data = $this->urlDataService->getEmbed($input->url);
            }
        } catch (UnfoldException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse($input->type === 'link' ? $data : ['html' => $data]);
    }
}
