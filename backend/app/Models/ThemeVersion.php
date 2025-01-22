<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Cast\BinaryCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $zip
 */
class ThemeVersion extends Model
{
    use HasFactory;

    protected $casts = [
        'zip' => BinaryCast::class
    ];
}
