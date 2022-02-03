<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Redirect\RedirectRepository;
use App\Domains\Redirect\Types\RedirectOutputType;

use App\Models\Blog;

class ConsoleRedirectController extends Controller {

    public function getRedirects(Blog $blog) {
        $getData = RedirectRepository::getRedirects($blog->id)
                ->map(function ($redirect) {
                    return new RedirectOutputType($redirect);
                });
        return response()->json($getData);
    }

    public function createRedirect(Request $request , Blog $blog) {
        $request->validate([
            'old_url' => 'required|string',
            'new_url' => 'required|string',
            'type' => 'required|int',
        ]);

        $oldURL = $request->input('old_url');
        $newURL = $request->input('new_url');
        $type = $request->input('type');

        $createRedirect = RedirectRepository::createRedirect($blog->id, $oldURL, $newURL, $type);
        return response()->json(new RedirectOutputType($createRedirect));
    }

    public function updateRedirect(Request $request, Blog $blog) {

        $request->validate([
            'old_url' => 'required|string',
            'new_url' => 'required|string',
            'type' => 'required|int',
        ]);

        $id = $request->route('id');
        $oldURL = $request->input('old_url');
        $newURL = $request->input('new_url');
        $type = $request->input('type');

        $updateRedirect = RedirectRepository::updateRedirect($blog->id, $id, $oldURL, $newURL, $type);
        return response()->json($updateRedirect);
    }

    public function deleteRedirect(Request $request) {
        $id = $request->route('id');
        $deleteRedirect = RedirectRepository::deleteRedirect($id);

        return response()->json($deleteRedirect);
    }
}