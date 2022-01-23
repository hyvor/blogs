<?php

namespace App\Domains\Subscription;

interface SubscriptionRepositoryInterface
{
    public function create(
        int $blogId,
        int $userId,
        string $userType,
        string $name,
        ?string $profileImage,
        string $type
    );

    public function update();

    public function getPlanNameByPlanId(int $planId);
}
