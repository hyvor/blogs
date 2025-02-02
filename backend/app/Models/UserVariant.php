<?php

namespace App\Models;

use Database\Factories\UserVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVariant extends Model
{

    /**
     * @use HasFactory<UserVariantFactory>
     */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
