<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Laravel\Paddle\Billable;

class Blog extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Billable;

}
