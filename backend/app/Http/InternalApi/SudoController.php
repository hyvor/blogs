<?php

namespace App\Http\InternalApi;

use Illuminate\Http\JsonResponse;
use App\Domains\Sudo\SudoAnalyticsService;


class SudoController
{
    public function overview(): JsonResponse
    {
        return response()->json([
            'blogs' => [
                'total' => SudoAnalyticsService::getBlogTotal(),
                'in_trial' => SudoAnalyticsService::getTrialBlogs(),
                'by_month' => SudoAnalyticsService::getBlogByMonth(),
                'paid'  => SudoAnalyticsService::getPaidBlogs(),
            ],
        ]);
    }
}
