<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Redirect\RedirectRepository;
use App\Data\Objects\ConsoleAPI\RedirectObject;
use App\Models\Blog;

class ConsoleRedirectController extends Controller {

    public function getRedirects(Blog $blog) {
        $getData = RedirectRepository::getRedirects($blog->id)
                ->map(function ($redirect) {
                return new RedirectObject($redirect);
            });
        return response()->json($getData);
    }

    public function createRedirect(Request $request , Blog $blog) {
        $regexValidation = 'regex:/(^([\/\:\.a-zA-z\-\*]+)(\d+)?$)/u';
        $request->validate([
            'old_url' => 'required|string|'.$regexValidation,
            'new_url' => 'required|string|'.$regexValidation,
            'type' => 'required|int',
        ]);
        $oldUrl = $request->input('old_url');
        $newUrl = $request->input('new_url');
        $type = $request->input('type');

        $createRedirect = RedirectRepository::createRedirect($blog->id, $oldUrl, $newUrl, $type);
        return response()->json(new RedirectObject($createRedirect));
    }

    public function updateRedirect(Request $request, Blog $blog) {
        $regexValidation = 'regex:/(^([\/\:\.a-zA-z\-\*]+)(\d+)?$)/u';
        $request->validate([
            'old_url' => 'required|string|'.$regexValidation,
            'new_url' => 'required|string|'.$regexValidation,
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