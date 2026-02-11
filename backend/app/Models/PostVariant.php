<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\PostStatusEnum;
use Carbon\Carbon;
use Database\Factories\PostVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $post_id
 * @property int $language_id
 * @property ?string $slug
 * @property PostStatusEnum $status
 * @property ?string $content
 * @property ?string $content_unsaved
 * @property ?string $content_html
 * @property ?string $content_text
 * @property ?string $title
 * @property ?string $description
 * @property ?int $words
 *
 * @property ?string $seo_primary_keyword
 * @property ?string[] $seo_secondary_keywords
 * @property ?array<string, number> $link_analysis
 *
 * @property Language $language
 * @property Post $post
 */
class PostVariant extends Model
{

    /**
     * @use HasFactory<PostVariantFactory>
     */
    use HasFactory;

    protected $casts = [
        'status' => PostStatusEnum::class,
        'seo_secondary_keywords' => 'array',
        'link_analysis' => 'array',
    ];

    protected $with = [
        'language',
    ];

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return HasMany<PostVariantHistory, $this>
     */
    public function history()
    {
        return $this->hasMany(PostVariantHistory::class);
    }
}
