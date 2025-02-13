<?php declare(strict_types=1);

namespace App\Models;

use Database\Factories\NavigationVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationVariant extends Model
{

    /**
     * @use HasFactory<NavigationVariantFactory>
     */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Navigation, $this>
     */
    public function navigation()
    {
        return $this->belongsTo(Navigation::class);
    }

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
