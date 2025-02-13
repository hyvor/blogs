<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up()
    {

        $query = <<<SQL
        CREATE EXTENSION IF NOT EXISTS citext;
        SQL;

        DB::unprepared($query);

    }

};
