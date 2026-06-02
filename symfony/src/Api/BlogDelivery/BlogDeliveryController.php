<?php

namespace App\Api\BlogDelivery;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogDeliveryController extends AbstractController
{

    #[Route('/blog/{subdomain}/{path}', defaults: ['path' => null], requirements: ['path' => '.*'], methods: ['GET'])]
    public function deliver(): Response
    {
        return new Response('Blog delivery API');
    }

}