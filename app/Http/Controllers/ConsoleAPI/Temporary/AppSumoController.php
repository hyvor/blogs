<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI\Temporary;

use App\Data\Enums\SubscriptionFrequencyEnum;
use App\Data\Enums\SubscriptionPlanEnum;
use App\Data\Enums\SubscriptionStatusEnum;
use App\Domains\Subscription\SubscriptionService;
use App\Exceptions\TrustedException;
use App\Models\AppsumoCode;
use App\Models\Blog;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppSumoController
{

    public function getCodes(Blog $blog) : JsonResponse
    {
        $codes = AppsumoCode::where('blog_id', $blog->id)
            ->orderBy('redeemed_at', 'ASC')
            ->pluck('code');
        return response()->json($codes);
    }

    public function redeem(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'code' => 'required|string'
        ]);

        $code = (string) $request->string('code');
        $codeModel = AppsumoCode::where('code', $code)->first();

        if (!$codeModel)
            throw new TrustedException('Code not found');

        if ($codeModel->blog_id)
            throw new TrustedException('Code already redeemed');

        $totalCodes = AppsumoCode::where('blog_id', $blog->id)->count();

        $plan = match ($totalCodes) {
            2 => SubscriptionPlanEnum::PREMIUM,
            1 => SubscriptionPlanEnum::GROWTH,
            default => SubscriptionPlanEnum::STARTER
        };

        $currentSubscription = SubscriptionService::getActiveBlogSubscription($blog);

        if ($currentSubscription) {
            $currentSubscription->status = SubscriptionStatusEnum::ACTIVE;
            $currentSubscription->plan = $plan;
            $currentSubscription->save();
        } else {
            Subscription::create([
                'blog_id' => $blog->id,
                'plan' => $plan,
                'frequency' => SubscriptionFrequencyEnum::MONTHLY,
                'status' => SubscriptionStatusEnum::ACTIVE,
            ]);
        }

        $codeModel->blog_id = $blog->id;
        $codeModel->redeemed_at = now();
        $codeModel->save();

        return response()->json();

    }

}