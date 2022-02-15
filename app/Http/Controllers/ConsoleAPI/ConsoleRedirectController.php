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
        
        $limit = $request->input('limit') ?? 50;
        $offset = $request->input('offset');

        $getData = RedirectRepository::getRedirects($blog->id, $limit, $offset)
                ->map(function ($redirect) {
                return new RedirectObject($redirect);
            });
        return response()->json($getData);
    }

    public function createRedirect(Request $request , Blog $blog) {
        // $url ='regex:/(^(https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,4}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)$)/u';
        // $path = 'regex:/(^([\/\:\.a-zA-z\-\*]+)(\d+)?$)/u';
        // $regexValidation = 'regex:/(^([\/\:\.a-zA-z0-9\-\?\_\=\*]+)(\d+)?$)/u';
        $regexValidation = get_called_class();
        $request->validate([
            'old_url' => 'required|string|'.$regexValidation::REGEX,
            'new_url' => 'required|string|'.$regexValidation::REGEX,
            'type' => 'required|int',
        ]);
        $oldUrl = $request->input('old_url');
        $newUrl = $request->input('new_url');
        $type = $request->input('type');

        $createRedirect = RedirectRepository::createRedirect($blog->id, $oldUrl, $newUrl, $type);
        return response()->json(new RedirectObject($createRedirect));
    }

    public function updateRedirect(Request $request, Blog $blog) {
        // $regexValidation = 'regex:/(^([\/\:\.a-zA-z0-9\-\?\_\=\*]+)(\d+)?$)/u';
        $regexValidation = get_called_class();
        $request->validate([
            'old_url' => 'required|string|'.$regexValidation::REGEX,
            'new_url' => 'required|string|'.$regexValidation::REGEX,
            'type' => 'required|int',
        ]);

        $id = $request->route('id');
        $oldUrl = $request->input('old_url');
        $newUrl = $request->input('new_url');
        $type = $request->input('type'); 

        $updateRedirect = RedirectRepository::updateRedirect($blog->id, $id, $oldUrl, $newUrl, $type);
        return response()->json($updateRedirect);
    }

    public function deleteRedirect(Request $request) {
        $id = $request->route('id');
        $deleteRedirect = RedirectRepository::deleteRedirect($id);

        return response()->json($deleteRedirect);
    }
}