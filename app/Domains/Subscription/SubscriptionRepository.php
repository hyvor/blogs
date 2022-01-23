<?php

namespace App\Domains\Subscription;

use App\Models\Blog;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    const PADDLE_PLANS = [
        'personal_pro' => [21525, null],
        'team' => [21526, 21527],
        'enterprise' => [21528, 21529]
    ];

    public function create(
        int $blogId,
        int $userId,
        string $userType,
        string $name,
        ?string $profileImage,
        string $type
    ) {
    }

    public function update()
    {
    }

    public function getPlanNameByPlanId(int $planId)
    {
        foreach (self::PADDLE_PLANS as $planName => $plan) {
            if ($plan[0] === $planId || $plan[1] === $planId) {
                return $planName;
            }
        }
    }
}
