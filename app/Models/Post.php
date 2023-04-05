<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    /** @var array<mixed> */
    protected $with = [
        'variants',
        'tags',
        'authors',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_page' => 'boolean',
    ];

    /**
     * @return BelongsTo<Blog, self>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * @return HasMany<PostVariant>
     */
    public function variants()
    {
        return $this->hasMany(PostVariant::class);
    }

    /**
     * @return BelongsToMany<Tag>
     */
    public function tags()
    {
        return $this
            ->belongsToMany(Tag::class)
            ->withPivot('post_tag.id')
            ->orderBy('post_tag.id', 'ASC');
    }

    /**
     * @return BelongsToMany<User>
     */
    public function authors()
    {
        return $this
            ->belongsToMany(User::class, 'post_author')
            ->withPivot('post_author.id')
            ->orderBy('post_author.id', 'ASC');
    }

}
