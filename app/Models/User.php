<?php

namespace App\Models;

use App\Models\Concerns\Countable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    use Countable;

    protected $with = [
        'variants',
    ];

    public function variants()
    {
        return $this->hasMany(UsersVariant::class);
    }


    public function blog()
    {
        return $this->belongsTo(Blog::class);
    } 

    public function media()
    {
        return $this->hasOne(Media::class, 'picture_id');
    }
}
 