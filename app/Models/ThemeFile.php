<?php

namespace App\Models;

use App\Data\Enums\ThemeFileFolderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeFile extends Model
{
    use HasFactory;

    protected $casts = [
        'folder' => ThemeFileFolderEnum::class,
    ];
}
