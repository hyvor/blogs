<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\UrlDataObject;
use App\Domains\UrlData\UrlDataRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsoleUrlDataController extends Controller
{
    public static function getData(Request $request)
    {
        $url = $request->input('url');
        $request->validate([
            'url' => 'required|url'
        ]);
        $embed = new UrlDataObject(UrlDataRepository::fetch($url));

        return response()->json($embed);
    }
}
