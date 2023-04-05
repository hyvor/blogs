<?php declare(strict_types=1);

namespace App\Models;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\PostSearchRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class PostVariant extends Model
{
    use HasFactory;
    use Searchable;

    protected $casts = [
        'status' => PostStatusEnum::class,
    ];

    /**
     * @var array<mixed>
     */
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

    public function searchableAs() : string
    {
        return PostSearchRepository::getIndexName();
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray()
    {
        return PostSearchRepository::getSearchDocument($this);
    }
}
