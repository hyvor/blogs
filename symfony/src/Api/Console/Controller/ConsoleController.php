<?php

namespace App\Api\Console\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ConsoleController
{

    #[Route('/init', methods: 'GET')]
    public function init(): JsonResponse {}

}
