<?php declare(strict_types=1);

namespace App\Domains\PostHistory;

use App\Models\PostVariant;
use App\Models\PostVariantHistory;

class PostHistoryService
{

    public static function createHistory(PostVariant $variant) : PostVariantHistory
    {

        $history = PostVariantHistory::create([
            'post_variant_id' => $variant->id,
            'content' => $variant->content,
        ]);

        self::trimHistory($variant);

        return $history;

    }

    public static function trimHistory(PostVariant $variant) : void
    {

        $history = PostVariantHistory::where('post_variant_id', $variant->id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->offset(25)
            ->get();

        foreach ($history as $item) {
            $item->delete();
        }
    }

}