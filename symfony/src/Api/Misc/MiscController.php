<?php

namespace App\Api\Misc;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MiscController extends AbstractController
{

    #[Route('/api/health', methods: ['GET'])]
    public function healthCheck(): Response
    {
        return new Response('OK', 200);
    }

}