<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Blog\UrlData\GetUrlDataInput;
use App\Service\UrlData\UrlDataFetchException;
use App\Service\UrlData\UrlDataFetchService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class UrlDataController
{
    public function __construct(private UrlDataFetchService $urlDataFetchService) {}

    #[Route('/url-data', methods: ['GET'])]
    public function getData(
        #[MapQueryString] GetUrlDataInput $input,
    ): JsonResponse {
        try {
            $data = $this->urlDataFetchService->fetch($input->url, $input->type);
        } catch (UrlDataFetchException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new JsonResponse($data);
    }
}
