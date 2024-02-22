<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Data\Objects\ConsoleAPI\UrlDataObject;
use App\Domains\UrlData\UrlDataRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsoleUrlDataController extends Controller
{
    public static function getData(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'type' => 'required|in:link,embed',
        ]);
        $url = $request->input('url');
        $type = $request->input('type');

        $embed = new UrlDataObject(
            UrlDataRepository::fetch(
                $url,
                UrlDataFetchTypeEnum::from($type)
            )
        );

        return response()->json($embed);
    }
}
