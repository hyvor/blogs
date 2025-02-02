<?php

namespace App\Models;

use Database\Factories\GptPromptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GptPrompt extends Model
{
    /**
     * @use HasFactory<GptPromptFactory>
     */
    use HasFactory;
    use SoftDeletes;
}
