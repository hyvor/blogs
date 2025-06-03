<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Domains\UrlData\UrlDataRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsoleUrlDataController extends Controller
{
    public function __construct(
        private readonly UrlDataRepository $urlDataRepository
    ) {}

    public function getData(Request $request) : JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'type' => 'required|in:link,embed',
        ]);
        $url = $request->input('url');
        $type = $request->input('type');

        return response()->json($this->urlDataRepository->fetch(
            $url,
            UrlDataFetchTypeEnum::from($type)
        ));
    }
}
