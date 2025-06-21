<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\LinkAnalyzer\LinkStatusCheck\IgnoreReasonEnum;
use Database\Factories\LinkAnalyzerLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?string $comment
 * @property ?IgnoreReasonEnum $ignore_reason
 */
class LinkAnalyzerLink extends Model
{

    /**
     * @use HasFactory<LinkAnalyzerLinkFactory>
     */
    use HasFactory;

    const UPDATED_AT = null;

    protected $casts = [
        'status_code' => 'integer',
        'ignore' => 'boolean',
        'ignore_reason' => IgnoreReasonEnum::class
    ];

    /**
     * @return BelongsTo<PostVariant, $this>
     */
    public function postVariant(): BelongsTo
    {
        return $this->belongsTo(PostVariant::class, 'post_variant_id');
    }

}
