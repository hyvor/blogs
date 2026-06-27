<?php

namespace App\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

class LetsEncryptController extends AbstractController
{
    public function __construct(
        private CacheInterface $cache,
    ) {}

    #[Route('/.well-known/acme-challenge/{token}', methods: 'GET')]
    public function resolveChallenge(string $token): Response
    {
        $keyAuth = $this->cache->get('acme_challenge_' . $token, function () {
            return null;
        });

        if (!$keyAuth) {
            return new Response('Not Found', 404);
        }

        return new Response($keyAuth, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}