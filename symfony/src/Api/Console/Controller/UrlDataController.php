<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Blog\UrlData\GetUrlDataInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UrlData\UrlDataService;
use Hyvor\Unfold\Exception\UnfoldException;

class UrlDataController
{
    public function __construct(private UrlDataService $urlDataService)
    {
    }

    #[Route('/url-data', methods: ['GET'])]
    public function getData(
        #[MapQueryString] GetUrlDataInput $input,
    ): JsonResponse {
        try {
            $data = $this->urlDataService->fetch($input->url, $input->type);
        } catch (UnfoldException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse($data);
    }
}
