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
        $request->validate([
            'old_url' => 'required|string|regex:/(^([\/a-zA-z\-]+)(\d+)?$)/u',
            'new_url' => 'required|string|regex:/(^([\/a-zA-z\-]+)(\d+)?$)/u',
            'type' => 'required|int',
        ]);
        $oldURL = $request->input('old_url');
        $newURL = $request->input('new_url');
        $type = $request->input('type');

        $createRedirect = RedirectRepository::createRedirect($blog->id, $oldURL, $newURL, $type);
        // return response()->json([
        //     new RedirectObject($createRedirect),
        //     'data'=> [ 
        //             'statusCode' =>  422,
        //             'message'=> "Invalid redirect"
        //     ],
        //     'status' => 200
        // ]);
        return response()->json(new RedirectObject($createRedirect));
        
    }

    public function updateRedirect(Request $request, Blog $blog) {

        $request->validate([
            'old_url' => 'required|string|regex:/(^([\/a-zA-z\-]+)(\d+)?$)/u',
            'new_url' => 'required|string|regex:/(^([\/a-zA-z\-]+)(\d+)?$)/u',
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