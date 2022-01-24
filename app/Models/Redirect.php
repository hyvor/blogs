<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $table = "redirects";

    public $timestamps=false;
    protected $fillable = [
        'blog_id',
        'old_url',
        'new_url',
        'type',
    ];
}
