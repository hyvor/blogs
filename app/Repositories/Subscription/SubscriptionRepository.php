<?php
namespace App\Repositories\Subscription;

use App\Models\Blog;

class SubscriptionRepository implements SubscriptionRepositoryInterface {

    public function create(
        int $blogId, int $userId, string $userType,
        string $name, ?string $profileImage, string $type
    ) {



    }

    public function update() {
        
    }

}