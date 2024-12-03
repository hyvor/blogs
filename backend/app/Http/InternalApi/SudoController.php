<?php

namespace App\Http\InternalApi;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Sudo\SudoAnalyticsService;
use App\Domains\Sudo\SudoDataService;


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

    public function getBlogs(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sort' => 'in:asc,desc|nullable',
            'filter' => 'in:in_trial,starter,growth,premium,business,enterprise|nullable',
            'limit' => 'integer|nullable',
            'offset' => 'integer|nullable',
        ]);

        $sortBy = $data['sort_by'] ?? 'id';
        $sort = $data['sort'] ?? 'desc';

        return response()->json(
            SudoDataService::blogs(
                $sortBy,
                $sort,
                $data['limit'] ?? 10,
                $data['offset'] ?? 0
            ),
        );
    }
}
