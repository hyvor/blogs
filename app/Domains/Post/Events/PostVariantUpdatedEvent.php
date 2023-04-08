<?php declare(strict_types=1);

namespace App\Domains\Post\Events;

use App\Models\PostVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostVariantUpdatedEvent
{
    use Dispatchable;

    public function __construct(
        public PostVariant $variant,
        public PostVariant $variantOld
    )
    {}
}
