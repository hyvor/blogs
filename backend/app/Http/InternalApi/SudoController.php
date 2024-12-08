<?php

namespace App\Http\InternalApi;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Sudo\SudoAnalyticsService;
use App\Domains\Sudo\SudoDataService;
use App\Domains\Sudo\SudoActionsService;
use App\Models\Blog;
use Hyvor\Internal\Http\Exceptions\HttpException;


class SudoController
{
    public function overview(): JsonResponse
    {
        return response()->json([
            'blogs' => [
                'total' => SudoAnalyticsService::getBlogTotal(),
                'total_30_days_change' => SudoAnalyticsService::getBlog30DaysChange(),
                'in_trial' => SudoAnalyticsService::getTrialBlogs(),
                'by_month' => SudoAnalyticsService::getBlogByMonth(),
                'paid'  => SudoAnalyticsService::getPaidBlogs(),
                'paid_30_days_change' => SudoAnalyticsService::getPaidBlogs30DaysChange(),
            ],
        ]);
    }

    public function getBlogs(Request $request): JsonResponse
    {
        $data = $request->validate([
            'blog_id' => 'integer|nullable',
            'subdomain' => 'string|nullable',
            'sort' => 'in:asc,desc|nullable',
            'filter' => 'in:in_trial,starter,growth,premium,business,team,enterprise|nullable',
            'limit' => 'integer|nullable',
            'offset' => 'integer|nullable',
        ]);

        $sortBy = $data['sort_by'] ?? 'id';
        $sort = $data['sort'] ?? 'desc';

        return response()->json(
            SudoDataService::blogs(
                $data['blog_id'] ?? null,
                $data['subdomain'] ?? null,
                $sortBy,
                $sort,
                $data['filter'] ?? null,
                $data['limit'] ?? 10,
                $data['offset'] ?? 0
            ),
        );
    }

    public function blogAction(Request $request, int $id): JsonResponse
    {
        $blog = Blog::find($id);
        if (!$blog) {
            throw new HttpException('Blog not found');
        }

        $data = $request->validate([
            'action' => 'required|string',
        ]);

        $action = $data['action'];

        if ($action === 'update_trial') {
            $trialEndsAt = $request->input('trial_ends_at');
            if (!$trialEndsAt) {
                throw new HttpException('trial_ends_at is required for update_trial action');
            }
            SudoActionsService::updateBlogTrial($blog, $trialEndsAt);
        } else if ($action === 'block') {
            SudoActionsService::blockBlog($blog);
        } else if ($action === 'unblock') {
            SudoActionsService::unblockBlog($blog);
        }
        else {
            throw new HttpException('Invalid action');
        }

        return response()->json();
    }
}
