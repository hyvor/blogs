<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagsVariant extends Model
{
    use HasFactory; 

    protected $table = 'tags_variants';

    public $timestamps = false;


    public function tag()
    {
        $this->belongTo(Tag::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    // public function counts() 
    // {
    //     return $this->morphMany(Count::class, 'countable');
    // }
    
}
