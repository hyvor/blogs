<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Redirect\RedirectRepository;
use App\Data\Objects\ConsoleAPI\RedirectObject;
use App\Models\Blog;

class ConsoleRedirectController extends Controller {

    const REGEX = 'regex:/(^([\/\:\.a-zA-z0-9\-\?\_\=\*]+)(\d+)?$)/u';

    public function getRedirects(Request $request, Blog $blog) {
        $request->validate([
            'limit' => 'integer', 
            'offset' => 'required|integer',
        ]);
        
        $limit = $request->input('limit');
        $offset = $request->input('offset');

        $getData = RedirectRepository::getRedirects($blog->id, $limit, $offset)
                ->map(function ($redirect) {
                return new RedirectObject($redirect);
            });
        return response()->json($getData);
    }

    public function createRedirect(Request $request , Blog $blog) {

        $regexValidation = get_called_class();
        $request->validate([
            'path' => 'required|string|'.$regexValidation::REGEX,
            'to' => 'required|string|'.$regexValidation::REGEX,
            'type' => 'required|int',
        ]);
        $path = $request->input('path');
        $to = $request->input('to');
        $type = $request->input('type');

        $createRedirect = RedirectRepository::createRedirect($blog->id, $path, $to, $type); 
        return response()->json(new RedirectObject($createRedirect));

    }

    public function updateRedirect(Request $request, Blog $blog) {
        $regexValidation = get_called_class();
        $request->validate([
            'path' => 'required|string|'.$regexValidation::REGEX,
            'to' => 'required|string|'.$regexValidation::REGEX,
            'type' => 'required|int',
        ]);

        $id = $request->route('id');
        $path = $request->input('path');
        $to = $request->input('to');
        $type = $request->input('type'); 

        $updateRedirect = RedirectRepository::updateRedirect($blog->id, $id, $path, $to, $type);
        return response()->json($updateRedirect);
    }

    public function deleteRedirect(Request $request) {
        $id = $request->route('id');
        $deleteRedirect = RedirectRepository::deleteRedirect($id);

        return response()->json($deleteRedirect);
    }
}