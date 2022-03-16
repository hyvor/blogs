<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersVariant extends Model
{
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
