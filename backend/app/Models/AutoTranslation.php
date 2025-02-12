<?php

namespace App\Models;

use App\Domains\Integrations\DeepL\Enums\DeepLSourceLangEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLTargetLangEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoTranslation extends Model
{
    // use HasFactory;

    protected $casts = [
        'source_lang' => DeepLSourceLangEnum::class,
        'target_lang' => DeepLTargetLangEnum::class,
        'chars' => 'int',
    ];

}
