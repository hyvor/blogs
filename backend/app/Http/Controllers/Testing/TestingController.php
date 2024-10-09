<?php declare(strict_types=1);

namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestingController extends Controller
{
    
    public function truncate() : JsonResponse
    {
        /*$tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        Schema::enableForeignKeyConstraints();*/

        return response()->json();
    }

    public function factory(Request $request) : JsonResponse
    {

        $model = (string) $request->string('model');
        $attrs = (array) $request->input('attrs');

        $modelClass = 'App\\Models\\' . $model;
        $models = app($modelClass)->factory()->create($attrs);

        return response()->json($models);

    }

    public function query(Request $request) : JsonResponse
    {

        $query = (string) $request->string('query');
        $results = DB::statement($query);
        return response()->json($results);

    }

}