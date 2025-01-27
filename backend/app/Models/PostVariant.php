<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\PostSearchRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostVariant extends Model
{
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
     * @return BelongsTo<Post, self>
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<Language, self>
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return HasMany<PostVariantHistory>
     */
    public function history()
    {
        return $this->hasMany(PostVariantHistory::class);
    }
}
