<?php

namespace App\Api\Public;

use App\Api\Public\Input\UnfoldIframeInput;
use App\Service\UrlData\UrlDataService;
use Hyvor\Unfold\Embed\Iframe\PrivacyIframe;
use Hyvor\Unfold\Exception\UnfoldException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class UnfoldController
{
    public function __construct(private UrlDataService $urlDataService)
    {
    }

    #[Route('/unfold/iframe', methods: ['GET'])]
    public function iframe(#[MapQueryString] UnfoldIframeInput $input): Response
    {
        try {
            $html = $this->urlDataService->getEmbed($input->url);
        } catch (UnfoldException) {
            $html = '';
        }

        $body = $html !== ''
            ? PrivacyIframe::wrap($html)
            : 'This URL cannot be embedded.';

        return new Response($body, Response::HTTP_OK, ['Content-Type' => 'text/html']);
    }
}
