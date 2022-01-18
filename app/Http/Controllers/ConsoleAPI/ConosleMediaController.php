<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsoleMediaController extends Controller {

    static function upload(Request $request) {
        $file = $request->file('file');
        
    }

}