<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeFile extends Model
{
    use HasFactory;
    protected $table = "theme_files";

    protected $fillable = [
       'theme_id',
       'name',
       'content',
    ];
}
